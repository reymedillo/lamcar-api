<?php

/*
 * This file is part of NotificationPusher.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Libs\NotificationPusher\Model;

use App\Libs\NotificationPusher\Collection\DeviceCollection;
use App\Libs\NotificationPusher\Adapter\AdapterInterface;
use App\Libs\NotificationPusher\Model\DeviceInterface;
use App\Libs\NotificationPusher\Model\MessageInterface;
use App\Libs\NotificationPusher\Exception\AdapterException;

/**
 * Push.
 */
class Push extends BaseOptionedModel implements PushInterface
{

    /**
     * @var string
     */
    private $status;

    /**
     * @var \App\Libs\NotificationPusher\Adapter\AdapterInterface
     */
    private $adapter;

    /**
     * @var \App\Libs\NotificationPusher\Model\MessageInterface
     */
    private $message;

    /**
     * @var \App\Libs\NotificationPusher\Collection\DeviceCollection
     */
    private $devices;

    /**
     * @var \DateTime
     */
    private $pushedAt;

    /**
     * Constructor.
     *
     * @param \App\Libs\NotificationPusher\Adapter\AdapterInterface  $adapter Adapter
     * @param DeviceInterface|DeviceCollection                           $devices Device(s)
     * @param \App\Libs\NotificationPusher\Model\MessageInterface             $message Message
     * @param array                                             $options Options
     *
     * Options are adapters specific ones, like Apns "badge" or "sound" option for example.
     * Of course, they can be more general.
     *
     * @throws \App\Libs\NotificationPusher\Exception\AdapterException
     */
    public function __construct(AdapterInterface $adapter, $devices, MessageInterface $message, array $options = array())
    {
        if ($devices instanceof DeviceInterface) {
            $devices = new DeviceCollection(array($devices));
        }

        $this->adapter = $adapter;
        $this->devices = $devices;
        $this->message = $message;
        $this->options = $options;
        $this->status  = self::STATUS_PENDING;

        $this->checkDevicesTokens();
    }

    /**
     * Check devices tokens.
     */
    private function checkDevicesTokens()
    {
        $devices = $this->getDevices();
        $adapter = $this->getAdapter();

        foreach ($devices as $device) {
            if (false === $adapter->supports($device->getToken())) {
                throw new AdapterException(
                    sprintf(
                        'Adapter %s does not supports %s token\'s device',
                        (string) $adapter,
                        $device->getToken()
                    )
                );
            }
        }
    }

    /**
     * Get Status.
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set Status.
     *
     * @param string $status Status
     *
     * @return \App\Libs\NotificationPusher\Model\PushInterface
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * isPushed.
     *
     * @return boolean
     */
    public function isPushed()
    {
        return (bool) (self::STATUS_PUSHED === $this->status);
    }

    /**
     * Declare as pushed.
     *
     * @return \App\Libs\NotificationPusher\Model\PushInterface
     */
    public function pushed()
    {
        $this->status   = self::STATUS_PUSHED;
        $this->pushedAt = new \DateTime();

        return $this;
    }

    /**
     * Get Adapter.
     *
     * @return \App\Libs\NotificationPusher\Adapter\AdapterInterface
     */
    public function getAdapter()
    {
        return $this->adapter;
    }

    /**
     * Set Adapter.
     *
     * @param \App\Libs\NotificationPusher\Adapter\AdapterInterface $adapter Adapter
     *
     * @return \App\Libs\NotificationPusher\Model\PushInterface
     */
    public function setAdapter(AdapterInterface $adapter)
    {
        $this->adapter = $adapter;

        return $this;
    }

    /**
     * Get Message.
     *
     * @return \App\Libs\NotificationPusher\Model\MessageInterface
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Set Message.
     *
     * @param \App\Libs\NotificationPusher\Model\MessageInterface $message Message
     *
     * @return \App\Libs\NotificationPusher\Model\PushInterface
     */
    public function setMessage(MessageInterface $message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Get Devices.
     *
     * @return \App\Libs\NotificationPusher\Collection\DeviceCollection
     */
    public function getDevices()
    {
        return $this->devices;
    }

    /**
     * Set Devices.
     *
     * @param \App\Libs\NotificationPusher\Collection\DeviceCollection $devices Devices
     *
     * @return \App\Libs\NotificationPusher\Model\PushInterface
     */
    public function setDevices(DeviceCollection $devices)
    {
        $this->devices = $devices;

        $this->checkDevicesTokens();

        return $this;
    }

    /**
     * Get PushedAt.
     *
     * @return \DateTime
     */
    public function getPushedAt()
    {
        return $this->pushedAt;
    }

    /**
     * Set PushedAt.
     *
     * @param \DateTime $pushedAt PushedAt
     *
     * @return \App\Libs\NotificationPusher\Model\PushInterface
     */
    public function setPushedAt(\DateTime $pushedAt)
    {
        $this->pushedAt = $pushedAt;

        return $this;
    }
}
