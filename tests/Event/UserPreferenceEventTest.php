<?php

/*
 * This file is part of the Kimai time-tracking app.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Event;

use App\Entity\User;
use App\Entity\UserPreference;
use App\Event\UserPreferenceEvent;
use PHPUnit\Framework\TestCase;

/**
 * @covers \App\Event\UserPreferenceEvent
 */
class UserPreferenceEventTest extends TestCase
{
    public function testGetterAndSetter()
    {
        $user = new User();
        $user->setAlias('foo');
        $pref = new UserPreference('foo', 'bar');

        $sut = new UserPreferenceEvent($user, []);

        $this->assertEquals($user, $sut->getUser());
        $this->assertEquals([], $sut->getPreferences());

        $sut->addPreference($pref);

        $this->assertEquals([$pref], $sut->getPreferences());
    }

    public function testDuplicatePreferenceThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);

        $user = new User();
        $user->setAlias('foo');
        $pref = new UserPreference('foo', 'bar');
        $pref2 = new UserPreference('foo', 'hello');

        $sut = new UserPreferenceEvent($user, []);

        $sut->addPreference($pref);
        $sut->addPreference($pref2);
    }
}
