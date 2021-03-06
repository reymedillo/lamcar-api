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
 * RuntimeException.
 *
 * @uses   \RuntimeException
 * @uses   \App\Libs\NotificationPusher\Exception\ExceptionInterface
 *
 */
class RuntimeException extends \RuntimeException implements ExceptionInterface
{
}
