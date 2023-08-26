<?php

/*
 * This file is part of the Kimai time-tracking app.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Controller\Reporting;

/**
 * @group integration
 */
class ReportUsersYearControllerTest extends AbstractUsersPeriodControllerTest
{
    protected function getReportUrl(): string
    {
        return '/reporting/users/year';
    }

    protected function getReportExportUrl(): string
    {
        return '/reporting/users/year_export';
    }

    protected function getBoxId(): string
    {
        return 'yearly-user-list-reporting-box';
    }
}
