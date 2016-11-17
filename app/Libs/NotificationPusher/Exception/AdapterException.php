<?php

/*
 * This file is part of NotificationPusher.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Libs\NotificationPusher\Exception;

use App\Libs\NotificationPusher\Exception\ExceptionInterface;

/**
 * AdapterException.
 *
 * @uses   \RuntimeException
 * @uses   \App\Libs\NotificationPusher\Exception\ExceptionInterface
 */
class AdapterException extends \RuntimeException implements ExceptionInterface
{
}
