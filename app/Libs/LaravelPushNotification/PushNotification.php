<?php namespace App\Libs\LaravelPushNotification;

class PushNotification {

    public function app($appName)
    {
        $config = is_array($appName) ? $appName : config('push-notification.'.$appName);
        return new App($config);
    }

    public function Message()
    {
      $instance = (new \ReflectionClass('App\Libs\NotificationPusher\Model\Message'));
        return $instance->newInstanceArgs(func_get_args());
    }

    public function Device()
    {
      $instance = (new \ReflectionClass('App\Libs\NotificationPusher\Model\Device'));
        return $instance->newInstanceArgs(func_get_args());
    }

    public function DeviceCollection()
    {
      $instance = (new \ReflectionClass('App\Libs\NotificationPusher\Collection\DeviceCollection'));
        return $instance->newInstanceArgs(func_get_args());
    }

    public function PushManager()
    {
      $instance = (new \ReflectionClass('App\Libs\NotificationPusher\PushManager'));
        return $instance->newInstanceArgs(func_get_args());
    }

    public function ApnsAdapter()
    {
      $instance = (new \ReflectionClass('App\Libs\NotificationPusher\Adapter\ApnsAdapter'));
        return $instance->newInstanceArgs(func_get_args());
    }

    public function GcmAdapter()
    {
      $instance = (new \ReflectionClass('App\Libs\NotificationPusher\Model\GcmAdapter'));
        return $instance->newInstanceArgs(func_get_args());
    }

    public function Push()
    {
      $instance = (new \ReflectionClass('App\Libs\NotificationPusher\Model\Push'));
        return $instance->newInstanceArgs(func_get_args());
    }

}
