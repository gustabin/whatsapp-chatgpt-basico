<?php

/*
 * This file is part of the Kimai time-tracking app.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Saml\Security;

use App\Entity\User;
use App\Saml\SamlToken;
use App\Saml\Security\SamlAuthenticationSuccessHandler;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\HttpUtils;

/**
 * @covers \App\Saml\Security\SamlAuthenticationSuccessHandler
 */
class SamlAuthenticationSuccessHandlerTest extends TestCase
{
    public function testWithAlwaysUseDefaultTargetPath()
    {
        $httpUtils = new HttpUtils($this->getUrlGenerator());
        $handler = new SamlAuthenticationSuccessHandler($httpUtils, ['always_use_default_target_path' => true]);
        $defaultTargetPath = $httpUtils->generateUri($this->getRequest('/sso/login'), $this->getOption($handler, 'default_target_path', '/'));
        $response = $handler->onAuthenticationSuccess($this->getRequest('/login', 'http://localhost/relayed'), $this->getSamlToken());
        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertTrue($response->isRedirect($defaultTargetPath));
    }

    public function testRelayState()
    {
        $handler = new SamlAuthenticationSuccessHandler(new HttpUtils($this->getUrlGenerator()), ['always_use_default_target_path' => false]);
        $response = $handler->onAuthenticationSuccess($this->getRequest('/sso/login', 'http://localhost/relayed'), $this->getSamlToken());
        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertTrue($response->isRedirect('http://localhost/relayed'));
    }

    public function testWithoutRelayState()
    {
        $httpUtils = new HttpUtils($this->getUrlGenerator());
        $handler = new SamlAuthenticationSuccessHandler($httpUtils, ['always_use_default_target_path' => false]);
        $defaultTargetPath = $httpUtils->generateUri($this->getRequest('/sso/login'), $this->getOption($handler, 'default_target_path', '/'));
        $response = $handler->onAuthenticationSuccess($this->getRequest(), $this->getSamlToken());
        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertTrue($response->isRedirect($defaultTargetPath));
    }

    public function testRelayStateLoop()
    {
        $httpUtils = new HttpUtils($this->getUrlGenerator());
        $handler = new SamlAuthenticationSuccessHandler($httpUtils, ['always_use_default_target_path' => false]);
        $loginPath = $httpUtils->generateUri($this->getRequest('/sso/login'), $this->getOption($handler, 'login_path', '/login'));
        $response = $handler->onAuthenticationSuccess($this->getRequest($loginPath), $this->getSamlToken());
        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertTrue(!$response->isRedirect($loginPath));
    }

    private function getUrlGenerator()
    {
        $urlGenerator = $this->getMockBuilder('Symfony\Component\Routing\Generator\UrlGeneratorInterface')->getMock();
        $urlGenerator
            ->expects($this->any())
            ->method('generate')
            ->will($this->returnCallback(function ($name) {
                return (string) $name;
            }))
        ;

        return $urlGenerator;
    }

    private function getRequest($path = '/', $relayState = null)
    {
        $params = [];
        if (null !== $relayState) {
            $params['RelayState'] = $relayState;
        }

        return Request::create($path, 'get', $params);
    }

    private function getSamlToken()
    {
        $user = new User();
        $user->setUserIdentifier('admin');

        $token = new SamlToken($user, 'secured_area', []);
        $token->setAttributes(['foo' => 'bar']);

        return $token;
    }

    private function getOption($handler, $name, $default = null)
    {
        $reflection = new \ReflectionObject($handler);
        $options = $reflection->getProperty('options');
        $options->setAccessible(true);
        $arr = $options->getValue($handler);
        if (!\is_array($arr) || !isset($arr[$name])) {
            return $default;
        }

        return $arr[$name];
    }
}
