<?php

namespace Nuwave\Lighthouse\Execution\Utils;

use Nuwave\Lighthouse\Subscriptions\SubscriptionRegistry;
use Nuwave\Lighthouse\Subscriptions\Contracts\BroadcastsSubscriptions;
use Nuwave\Lighthouse\Support\Contracts\SubscriptionExceptionHandler as ExceptionHandler;

class Subscription
{
    /**
     * Broadcast subscription to client(s).
     *
     * @param  string  $subscriptionField
     * @param  mixed  $root
     * @param  bool|null  $queue
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    public static function broadcast(string $subscriptionField, $root, ?bool $queue = null): void
    {
        // Ensure we have a schema and registered subscription fields
        // in the event we are calling this method in code.
        app('graphql')->prepSchema();

        /** @var \Nuwave\Lighthouse\Subscriptions\SubscriptionRegistry $registry */
        $registry = app(SubscriptionRegistry::class);
        /** @var \Nuwave\Lighthouse\Subscriptions\Contracts\BroadcastsSubscriptions $broadcaster */
        $broadcaster = app(BroadcastsSubscriptions::class);
        /** @var \Nuwave\Lighthouse\Support\Contracts\SubscriptionExceptionHandler $exceptionHandler */
        $exceptionHandler = app(ExceptionHandler::class);

        if (! $registry->has($subscriptionField)) {
            throw new \InvalidArgumentException("No subscription field registered for {$subscriptionField}");
        }

        $queue = $queue === null ? config('lighthouse.subscriptions.queue_broadcasts', false) : $queue;
        $method = $queue ? 'queueBroadcast' : 'broadcast';
        $subscription = $registry->subscription($subscriptionField);

        try {
            call_user_func(
                [$broadcaster, $method],
                $subscription,
                $subscriptionField,
                $root
            );
        } catch (\Throwable $e) {
            $exceptionHandler->handleBroadcastError($e);
        }
    }
}
