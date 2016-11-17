<?php

/*
 * This file is part of NotificationPusher.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Libs\NotificationPusher\Model;

/**
 * Message.
 */
class Message extends BaseOptionedModel implements MessageInterface
{
    /**
     * @var string
     */
    private $text;

    /**
     * Constructor.
     *
     * @param string $text    Text
     * @param array  $options Options
     */
    public function __construct($text, array $options = array())
    {
        $this->text    = $text;
        $this->options = $options;
    }

    /**
     * Get Text.
     *
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Set Text.
     *
     * @param string $text Text
     *
     * @return \App\Libs\NotificationPusher\Model\MessageInterface
     */
    public function setText($text)
    {
        $this->text = $text;

        return $this;
    }
}
