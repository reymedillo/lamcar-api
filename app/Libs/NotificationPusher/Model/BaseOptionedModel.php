<?php

/*
 * This file is part of NotificationPusher.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Libs\NotificationPusher\Model;

/**
 * BaseOptionedModel.
 */
abstract class BaseOptionedModel
{
    /**
     * @var array
     */
    protected $options = array();

    /**
     * Get options.
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Has option.
     *
     * @param string $key Key
     *
     * @return boolean
     */
    public function hasOption($key)
    {
        return array_key_exists($key, $this->options);
    }

    /**
     * Get option.
     *
     * @param string $key     Key
     * @param mixed  $default Default
     *
     * @return mixed
     */
    public function getOption($key, $default = null)
    {
        return $this->hasOption($key) ? $this->options[$key] : $default;
    }

    /**
     * Set options.
     *
     * @param array $options Options
     *
     * @return \App\Libs\NotificationPusher\Model\BaseOptionedModel
     */
    public function setOptions($options)
    {
        $this->options = $options;

        return $this;
    }

    /**
     * Set option.
     *
     * @param string $key   Key
     * @param mixed  $value Value
     *
     * @return mixed
     */
    public function setOption($key, $value)
    {
        $this->options[$key] = $value;

        return $value;
    }
}
