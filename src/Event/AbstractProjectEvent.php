<?php

/*
 * This file is part of the Kimai time-tracking app.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Event;

use App\Entity\Project;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Base event class to used with project manipulations.
 */
abstract class AbstractProjectEvent extends Event
{
    public function __construct(private Project $project)
    {
    }

    public function getProject(): Project
    {
        return $this->project;
    }
}
