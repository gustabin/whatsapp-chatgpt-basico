<?php

/*
 * This file is part of the Kimai time-tracking app.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Model;

use DateTimeInterface;

class Year
{
    /**
     * @var Month[]
     */
    private array $months = [];

    public function __construct(private DateTimeInterface $month)
    {
        $monthDate = new \DateTimeImmutable();
        $monthDate = $monthDate->setDate((int) $this->month->format('Y'), 1, 1);
        $monthDate = $monthDate->setTime(1, 0);
        for ($i = 1; $i < 13; $i++) {
            $month = $this->createMonth($monthDate);
            $this->setMonth($month);
            $monthDate = $monthDate->add(new \DateInterval('P1M'));
        }
    }

    protected function createMonth(\DateTimeInterface $month): Month
    {
        return new Month($month);
    }

    public function getYear(): DateTimeInterface
    {
        return $this->month;
    }

    protected function setMonth(Month $month): void
    {
        $this->months['_' . $month->getMonth()->format('m')] = $month;
    }

    public function getMonth(\DateTimeInterface $month): Month
    {
        return $this->months['_' . $month->format('m')];
    }

    public function getDay(\DateTimeInterface $date): Day
    {
        return $this->getMonth($date)->getDay($date);
    }

    /**
     * @return Month[]
     */
    public function getMonths(): array
    {
        return array_values($this->months);
    }
}
