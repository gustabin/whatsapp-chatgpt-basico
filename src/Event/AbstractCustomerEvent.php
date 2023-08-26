<?php

/*
 * This file is part of the Kimai time-tracking app.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Event;

use App\Entity\Customer;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Base event class to used with customer manipulations.
 */
abstract class AbstractCustomerEvent extends Event
{
    public function __construct(private Customer $customer)
    {
    }

    public function getCustomer(): Customer
    {
        return $this->customer;
    }
}
