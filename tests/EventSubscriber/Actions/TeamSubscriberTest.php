<?php

/*
 * This file is part of the Kimai time-tracking app.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\EventSubscriber\Actions;

use App\EventSubscriber\Actions\TeamSubscriber;

/**
 * @covers \App\EventSubscriber\Actions\TeamSubscriber
 */
class TeamSubscriberTest extends AbstractActionsSubscriberTest
{
    public function testEventName()
    {
        $this->assertGetSubscribedEvent(TeamSubscriber::class, 'team');
    }
}
