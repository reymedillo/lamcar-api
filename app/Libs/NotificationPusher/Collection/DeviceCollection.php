<?php

/*
 * This file is part of NotificationPusher.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Libs\NotificationPusher\Collection;

use App\Libs\NotificationPusher\Model\DeviceInterface;

/**
 * DeviceCollection.
 *
 * @uses \App\Libs\NotificationPusher\Collection\AbstractCollection
 * @uses \IteratorAggregate
 */
class DeviceCollection extends AbstractCollection implements \IteratorAggregate
{
    /**
     * Constructor.
     *
     * @param array $devices Devices
     */
    public function __construct(array $devices = array())
    {
        $this->coll = new \ArrayIterator();

        foreach ($devices as $device) {
            $this->add($device);
        }
    }

    /**
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return $this->coll;
    }

    /**
     * @param \App\Libs\NotificationPusher\Model\DeviceInterface $device Device
     */
    public function add(DeviceInterface $device)
    {
        $this->coll[$device->getToken()] = $device;
    }

    /**
     * Get tokens.
     *
     * @return array
     */
    public function getTokens()
    {
        $tokens = array();

        foreach ($this as $token => $device) {
            $tokens[] = $token;
        }

        return array_unique(array_filter($tokens));
    }
}
