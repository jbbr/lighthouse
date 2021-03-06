<?php

namespace Nuwave\Lighthouse\Subscriptions;

use Pusher\Pusher;
use Nuwave\Lighthouse\Support\DriverManager;
use Nuwave\Lighthouse\Subscriptions\Contracts\Broadcaster;
use Nuwave\Lighthouse\Subscriptions\Broadcasters\LogBroadcaster;
use Nuwave\Lighthouse\Subscriptions\Broadcasters\PusherBroadcaster;

/**
 * @method void broadcast(\Nuwave\Lighthouse\Subscriptions\Subscriber $subscriber, array $data)
 * @method \Symfony\Component\HttpFoundation\Response hook(\Illuminate\Http\Request $request)
 * @method \Symfony\Component\HttpFoundation\Response authorized(\Illuminate\Http\Request $request)
 * @method \Symfony\Component\HttpFoundation\Response unauthorized(\Illuminate\Http\Request $request)
 */
class BroadcastManager extends DriverManager
{
    /**
     * Get configuration key.
     *
     * @return string
     */
    protected function configKey(): string
    {
        return 'lighthouse.subscriptions.broadcasters';
    }

    /**
     * Get configuration driver key.
     *
     * @return string
     */
    protected function driverKey(): string
    {
        return 'lighthouse.subscriptions.broadcaster';
    }

    /**
     * The interface the driver should implement.
     *
     * @return string
     */
    protected function interface(): string
    {
        return Broadcaster::class;
    }

    /**
     * Create instance of pusher driver.
     *
     * @param  mixed[]  $config
     * @return \Nuwave\Lighthouse\Subscriptions\Broadcasters\PusherBroadcaster
     * @throws \Pusher\PusherException
     */
    protected function createPusherDriver(array $config): PusherBroadcaster
    {
        $appKey = config('broadcasting.connections.pusher.key');
        $appSecret = config('broadcasting.connections.pusher.secret');
        $appId = config('broadcasting.connections.pusher.app_id');
        $options = config('broadcasting.connections.pusher.options', []);

        $pusher = new Pusher($appKey, $appSecret, $appId, $options);

        return new PusherBroadcaster($pusher);
    }

    /**
     * Create instance of log driver.
     *
     * @param  mixed[]  $config
     * @return \Nuwave\Lighthouse\Subscriptions\Broadcasters\LogBroadcaster
     */
    protected function createLogDriver(array $config): LogBroadcaster
    {
        return new LogBroadcaster($config);
    }
}
