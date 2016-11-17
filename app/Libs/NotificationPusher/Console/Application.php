<?php

/*
 * This file is part of NotificationPusher.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Libs\NotificationPusher\Console;

use Symfony\Component\Console\Application as BaseApplication;
use App\Libs\NotificationPusher\NotificationPusher;
use App\Libs\NotificationPusher\Console\Command\PushCommand;

/**
 * Application.
 *
 * @uses \Symfony\Component\Console\Application
 */
class Application extends BaseApplication
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        error_reporting(-1);

        parent::__construct('NotificationPusher version', NotificationPusher::VERSION);

        $this->add(new PushCommand());
    }
}
