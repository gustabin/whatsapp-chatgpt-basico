<?php

/*
 * This file is part of the Kimai time-tracking app.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\API;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class NotFoundException extends NotFoundHttpException
{
    public function __construct(string $message = 'Not found', \Exception $previous = null, int $code = 404, array $headers = [])
    {
        parent::__construct($message, $previous, $code, $headers);
    }
}
