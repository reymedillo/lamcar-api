<?php

/*
 * This file is part of NotificationPusher.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Libs\NotificationPusher\Adapter;

use App\Libs\NotificationPusher\Model\PushInterface;
use App\Libs\NotificationPusher\Model\MessageInterface;
use App\Libs\NotificationPusher\Model\DeviceInterface;
use App\Libs\NotificationPusher\Exception\AdapterException;
use App\Libs\NotificationPusher\Exception\PushException;
use App\Libs\NotificationPusher\Collection\DeviceCollection;

use ZendService\Apple\Apns\Client\AbstractClient as ServiceAbstractClient;
use ZendService\Apple\Apns\Client\Message as ServiceClient;
use ZendService\Apple\Apns\Message as ServiceMessage;
use ZendService\Apple\Apns\Message\Alert as ServiceAlert;
use ZendService\Apple\Apns\Response\Message as ServiceResponse;
use ZendService\Apple\Apns\Exception\RuntimeException as ServiceRuntimeException;
use ZendService\Apple\Apns\Client\Feedback as ServiceFeedbackClient;

/**
 * APNS adapter.
 *
 * @uses \App\Libs\NotificationPusher\Adapter\BaseAdapter
 * @uses \App\Libs\NotificationPusher\Adapter\AdapterInterface
 *
 */
class Apns extends BaseAdapter implements AdapterInterface
{
    /**
     * {@inheritdoc}
     *
     * @throws \App\Libs\NotificationPusher\Exception\AdapterException
     */
    public function __construct(array $parameters = array())
    {
        parent::__construct($parameters);

        $cert = $this->getParameter('certificate');

        if (false === file_exists($cert)) {
            throw new AdapterException(sprintf('Certificate %s does not exist', $cert));
        }
    }

    /**
     * {@inheritdoc}
     *
     * @throws \App\Libs\NotificationPusher\Exception\PushException
     */
    public function push(PushInterface $push)
    {
        $client = $this->getOpenedClient(new ServiceClient());

        $pushedDevices = new DeviceCollection();

        foreach ($push->getDevices() as $device) {
            $message = $this->getServiceMessageFromOrigin($device, $push->getMessage());

            try {
                $this->response = $client->send($message);
            } catch (ServiceRuntimeException $e) {
                throw new PushException($e->getMessage());
            }

            if (ServiceResponse::RESULT_OK === $this->response->getCode()) {
                $pushedDevices->add($device);
            }
        }

        $client->close();

        return $pushedDevices;
    }

    /**
     * Feedback.
     *
     * @return array
     */
    public function getFeedback()
    {
        $client           = $this->getOpenedClient(new ServiceFeedbackClient());
        $responses        = array();
        $serviceResponses = $client->feedback();
        $client->close();

        foreach ($serviceResponses as $response) {
            $responses[$response->getToken()] = new \DateTime(date("c", $response->getTime()));
        }

        return $responses;
    }

    /**
     * Get opened client.
     *
     * @param \ZendService\Apple\Apns\Client\AbstractClient $client Client
     *
     * @return \ZendService\Apple\Apns\Client\AbstractClient
     */
    public function getOpenedClient(ServiceAbstractClient $client)
    {
        $client->open(
            $this->isProductionEnvironment() ? ServiceClient::PRODUCTION_URI : ServiceClient::SANDBOX_URI,
            $this->getParameter('certificate'),
            $this->getParameter('passPhrase')
        );

        return $client;
    }

    /**
     * Get service message from origin.
     *
     * @param \App\Libs\NotificationPusher\Model\DeviceInterface  $device  Device
     * @param \App\Libs\NotificationPusher\Model\MessageInterface $message Message
     *
     * @return \ZendService\Apple\Apns\Message
     */
    public function getServiceMessageFromOrigin(DeviceInterface $device, MessageInterface $message)
    {
        $badge = ($message->hasOption('badge'))
            ? (int) ($message->getOption('badge') + $device->getParameter('badge', 0))
            : 0
        ;

        $sound = $message->getOption('sound', 'bingbong.aiff');
        $contentAvailable = $message->getOption('content-available');

        $alert = new ServiceAlert(
            $message->getText(),
            $message->getOption('actionLocKey'),
            $message->getOption('locKey'),
            $message->getOption('locArgs'),
            $message->getOption('launchImage')
        );
        if ($actionLocKey = $message->getOption('actionLocKey')) {
            $alert->setActionLocKey($actionLocKey);
        }
        if ($locKey = $message->getOption('locKey')) {
            $alert->setLocKey($locKey);
        }
        if ($locArgs = $message->getOption('locArgs')) {
            $alert->setLocArgs($locArgs);
        }
        if ($launchImage = $message->getOption('launchImage')) {
            $alert->setLaunchImage($launchImage);
        }

        $serviceMessage = new ServiceMessage();
        $serviceMessage->setId(sha1($device->getToken().$message->getText()));
        $serviceMessage->setAlert($alert);
        $serviceMessage->setToken($device->getToken());
        $serviceMessage->setBadge($badge);
        $serviceMessage->setCustom($message->getOption('custom', array()));

        if (null !== $sound) {
            $serviceMessage->setSound($sound);
        }

        if (null !== $contentAvailable) {
            $serviceMessage->setContentAvailable($contentAvailable);
        }

        return $serviceMessage;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($token)
    {
        return (ctype_xdigit($token) && 64 == strlen($token));
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultParameters()
    {
        return array('passPhrase' => null);
    }

    /**
     * {@inheritdoc}
     */
    public function getRequiredParameters()
    {
        return array('certificate');
    }
}
