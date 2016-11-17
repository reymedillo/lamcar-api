<?php

/*
 * This file is part of NotificationPusher.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Libs\NotificationPusher\Model;

/**
 * MessageInterface
 */
interface MessageInterface
{
    /**
     * Get Text.
     *
     * @return string
     */
    public function getText();

    /**
     * Set Text.
     *
     * @param string $text Text
     *
     * @return \App\Libs\NotificationPusher\Model\MessageInterface
     */
    public function setText($text);
}
