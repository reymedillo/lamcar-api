<?php

/*
 * This file is part of NotificationPusher.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Libs\NotificationPusher\Model;

/**
 * DeviceInterface
 */
interface DeviceInterface
{
    /**
     * Get token.
     *
     * @return string
     */
    public function getToken();

    /**
     * Set token.
     *
     * @param string $token Token
     *
     * @return \App\Libs\NotificationPusher\Model\DeviceInterface
     */
    public function setToken($token);
}
