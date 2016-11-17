<?php

/*
 * This file is part of NotificationPusher.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Libs\NotificationPusher\Model;

/**
 * Device.
 */
class Device extends BaseParameteredModel implements DeviceInterface
{
    /**
     * @var string
     */
    private $token;

    /**
     * Constructor.
     *
     * @param string $token      Token
     * @param array  $parameters Parameters
     */
    public function __construct($token, array $parameters = array())
    {
        $this->token      = $token;
        $this->parameters = $parameters;
    }

    /**
     * Get token.
     *
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Set token.
     *
     * @param string $token Token
     *
     * @return \App\Libs\NotificationPusher\Model\DeviceInterface
     */
    public function setToken($token)
    {
        $this->token = $token;

        return $this;
    }
}
