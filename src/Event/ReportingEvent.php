<?php

/*
 * This file is part of the Kimai time-tracking app.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Event;

use App\Entity\User;
use App\Reporting\ReportInterface;
use Symfony\Contracts\EventDispatcher\Event;

final class ReportingEvent extends Event
{
    /**
     * @var array<string, ReportInterface>
     */
    private array $reports = [];

    public function __construct(private User $user)
    {
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function addReport(ReportInterface $report): ReportingEvent
    {
        $this->reports[$report->getId()] = $report;

        return $this;
    }

    /**
     * @return array<ReportInterface>
     */
    public function getReports(): array
    {
        return array_values($this->reports);
    }
}
