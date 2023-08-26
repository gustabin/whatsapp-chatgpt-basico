<?php

/*
 * This file is part of the Kimai time-tracking app.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Widget\Type;

final class Counter extends AbstractSimpleStatisticChart
{
    public function getTemplateName(): string
    {
        return 'widget/widget-counter.html.twig';
    }
}
