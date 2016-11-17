<?php

/*
 * This file is part of NotificationPusher.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Libs\NotificationPusher\Adapter;

use App\Libs\NotificationPusher\Model\PushInterface;

/**
 * AdapterInterface.
 */
interface AdapterInterface
{
    /**
     * Push.
     *
     * @param \App\Libs\NotificationPusher\Model\PushInterface $push Push
     *
     * @return \App\Libs\NotificationPusher\Collection\DeviceCollection
     */
    public function push(PushInterface $push);

    /**
     * Supports.
     *
     * @param string $token Token
     *
     * @return boolean
     */
    public function supports($token);

    /**
     * Get default parameters.
     *
     * @return array
     */
    public function getDefaultParameters();

    /**
     * Get required parameters.
     *
     * @return array
     */
    public function getRequiredParameters();
}
