<?php

/*
 * This file is part of the Kimai time-tracking app.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Invoice\Renderer;

use App\Invoice\Renderer\TwigRenderer;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

/**
 * @covers \App\Invoice\Renderer\AbstractTwigRenderer
 * @covers \App\Invoice\Renderer\TwigRenderer
 * @group integration
 */
class TwigRendererTest extends KernelTestCase
{
    use RendererTestTrait;

    public function testSupports()
    {
        $loader = new FilesystemLoader();
        $env = new Environment($loader);
        $sut = new TwigRenderer($env);

        $this->assertTrue($sut->supports($this->getInvoiceDocument('invoice.html.twig')));
        $this->assertTrue($sut->supports($this->getInvoiceDocument('timesheet.html.twig')));
        $this->assertFalse($sut->supports($this->getInvoiceDocument('service-date.pdf.twig')));
        $this->assertFalse($sut->supports($this->getInvoiceDocument('company.docx', true)));
        $this->assertFalse($sut->supports($this->getInvoiceDocument('spreadsheet.xlsx', true)));
        $this->assertFalse($sut->supports($this->getInvoiceDocument('open-spreadsheet.ods', true)));
    }

    public function testRender()
    {
        $kernel = self::bootKernel();
        /** @var Environment $twig */
        $twig = self::getContainer()->get('twig');
        $stack = self::getContainer()->get('request_stack');
        $request = new Request();
        $request->setLocale('en');
        $stack->push($request);

        /** @var FilesystemLoader $loader */
        $loader = $twig->getLoader();
        $loader->addPath($this->getInvoiceTemplatePath(), 'invoice');

        $sut = new TwigRenderer($twig);

        $model = $this->getInvoiceModel();
        $model->getTemplate()->setLanguage('de');

        $document = $this->getInvoiceDocument('timesheet.html.twig');
        $response = $sut->render($document, $model);

        $content = $response->getContent();

        $filename = $model->getInvoiceNumber() . '-customer_with_special_name';
        $this->assertStringContainsString('<title>' . $filename . '</title>', $content);
        $this->assertStringContainsString('<span contenteditable="true">a very *long* test invoice / template title with [ßpecial] chäracter</span>', $content);
        // 3 timesheets have a description and therefor do not render the activity
        // 2 timesheets have no description and the correct activity assigned
        $this->assertEquals(2, substr_count($content, 'activity description'));
        $this->assertStringContainsString(nl2br("foo\n" .
    "foo\r\n" .
    'foo' . PHP_EOL .
    "bar\n" .
    "bar\r\n" .
    'Hello'), $content);
    }
}
