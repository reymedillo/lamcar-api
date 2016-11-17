<?php

/*
 * This file is part of NotificationPusher.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Libs\NotificationPusher\Adapter;

use Symfony\Component\OptionsResolver\OptionsResolver;

use App\Libs\NotificationPusher\Model\BaseParameteredModel;
use App\Libs\NotificationPusher\PushManager;

/**
 * BaseAdapter.
 */
abstract class BaseAdapter extends BaseParameteredModel
{
    /**
     * @var string
     */
    protected $adapterKey;

    /**
     * @var string
     */
    protected $environment;

    /**
     * @var mixed
     */
    protected $response;

    /**
     * Constructor.
     *
     * @param array $parameters Adapter specific parameters
     */
    public function __construct(array $parameters = array())
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults($this->getDefaultParameters());
        $resolver->setRequired($this->getRequiredParameters());

        $reflectedClass   = new \ReflectionClass($this);
        $this->adapterKey = lcfirst($reflectedClass->getShortName());
        $this->parameters = $resolver->resolve($parameters);
    }

    /**
     * __toString.
     *
     * @return string
     */
    public function __toString()
    {
        return ucfirst($this->getAdapterKey());
    }

    /**
     * Return the original response.
     * 
     * @return mixed
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Get AdapterKey.
     *
     * @return string
     */
    public function getAdapterKey()
    {
        return $this->adapterKey;
    }

    /**
     * Get Environment.
     *
     * @return string
     */
    public function getEnvironment()
    {
        return $this->environment;
    }

    /**
     * Set Environment.
     *
     * @param string $environment Environment value to set
     *
     * @return \App\Libs\NotificationPusher\Adapter\AdapterInterface
     */
    public function setEnvironment($environment)
    {
        $this->environment = $environment;

        return $this;
    }

    /**
     * isDevelopmentEnvironment.
     *
     * @return boolean
     */
    public function isDevelopmentEnvironment()
    {
        return (PushManager::ENVIRONMENT_DEV === $this->getEnvironment());
    }

    /**
     * isProductionEnvironment.
     *
     * @return boolean
     */
    public function isProductionEnvironment()
    {
        return (PushManager::ENVIRONMENT_PROD === $this->getEnvironment());
    }
}
