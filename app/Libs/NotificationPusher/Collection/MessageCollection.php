<?php

/*
 * This file is part of NotificationPusher.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Libs\NotificationPusher\Collection;

use App\Libs\NotificationPusher\Model\MessageInterface;

/**
 * MessageCollection.
 *
 * @uses \App\Libs\NotificationPusher\Collection\AbstractCollection
 * @uses \IteratorAggregate
 */
class MessageCollection extends AbstractCollection implements \IteratorAggregate
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->coll = new \ArrayIterator();
    }

    /**
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return $this->coll;
    }

    /**
     * @param \App\Libs\NotificationPusher\Model\MessageInterface $message Message
     */
    public function add(MessageInterface $message)
    {
        $this->coll[] = $message;
    }
}
