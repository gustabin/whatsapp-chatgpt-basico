<?php

/*
 * This file is part of the Kimai time-tracking app.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Security;

use App\Security\SessionHandler;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\TestCase;

/**
 * @covers \App\Security\SessionHandler
 */
class SessionHandlerTest extends TestCase
{
    public function testConstruct()
    {
        $sut = new SessionHandler($this->createMock(Connection::class));

        self::assertFalse($sut->isSessionExpired());
    }
}
