<?php

/*
 * This file is part of the Kimai time-tracking app.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Model;

use App\Entity\Activity;
use App\Model\ActivityStatistic;

/**
 * @covers \App\Model\ActivityStatistic
 */
class ActivityStatisticTest extends AbstractTimesheetCountedStatisticTest
{
    public function testDefaultValues()
    {
        $this->assertDefaultValues(new ActivityStatistic());
    }

    public function testSetter()
    {
        $this->assertSetter(new ActivityStatistic());
    }

    public function testJsonSerialize()
    {
        $this->assertJsonSerialize(new ActivityStatistic());
    }

    public function testAdditionalSetter()
    {
        $sut = new ActivityStatistic();
        self::assertNull($sut->getActivity());
        self::assertNull($sut->getColor());
        self::assertNull($sut->getName());

        $activity = new Activity();
        $sut->setActivity($activity);
        $this->assertEquals($activity, $sut->getActivity());

        self::assertNull($sut->getColor());
        self::assertNull($sut->getName());

        $activity->setName('FOO');
        self::assertEquals('FOO', $sut->getName());

        $activity->setColor('#000000');
        self::assertEquals('#000000', $sut->getColor());

        $json = $sut->jsonSerialize();
        self::assertEquals('FOO', $json['name']);
        self::assertEquals('#000000', $json['color']);
    }
}
