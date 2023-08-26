<?php

/*
 * This file is part of the Kimai time-tracking app.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Entity;

use App\Entity\Role;
use PHPUnit\Framework\TestCase;

/**
 * @covers \App\Entity\Role
 */
class RoleTest extends TestCase
{
    public function testDefaultValues(): void
    {
        $sut = new Role();
        self::assertNull($sut->getId());
        self::assertNull($sut->getName());
    }

    public function testSetterAndGetter(): void
    {
        $sut = new Role();

        $sut->setName('foo');
        self::assertEquals('FOO', $sut->getName());

        $sut->setName('BAR');
        self::assertEquals('BAR', $sut->getName());
    }
}
