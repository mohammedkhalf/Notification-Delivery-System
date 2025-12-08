<?php

namespace App\Providers;

use App\Domain\Channels\ChannelInterface;
use App\Domain\Repositories\NotificationRepositoryInterface;
use App\Domain\Routing\RoutingEngineInterface;
use App\Infrastructure\Channels\EmailChannel;
use App\Infrastructure\Channels\PushChannel;
use App\Infrastructure\Channels\SmsChannel;
use App\Infrastructure\Channels\WebhookChannel;
use App\Infrastructure\Repositories\NotificationRepository;
use App\Infrastructure\Routing\ConfigBasedRoutingEngine;
use Illuminate\Support\ServiceProvider;

class NotificationServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Bind repository
        $this->app->bind(
            NotificationRepositoryInterface::class,
            NotificationRepository::class
        );

        // Bind routing engine
        $this->app->bind(
            RoutingEngineInterface::class,
            ConfigBasedRoutingEngine::class
        );

        // Register channels
        $this->app->singleton('notification.channels', function ($app) {
            return [
                'email' => $app->make(EmailChannel::class),
                'sms' => $app->make(SmsChannel::class),
                'push' => $app->make(PushChannel::class),
                'webhook' => $app->make(WebhookChannel::class),
            ];
        });

        // Bind channels to NotificationDeliveryService
        $this->app->when(\App\Application\Services\NotificationDeliveryService::class)
            ->needs('$channels')
            ->give(function ($app) {
                return $app->make('notification.channels');
            });

        // Bind retry configuration
        $this->app->when(\App\Application\Services\RetryService::class)
            ->needs('$maxAttempts')
            ->give(config('notification.retry.max_attempts', 3));

        $this->app->when(\App\Application\Services\RetryService::class)
            ->needs('$baseDelaySeconds')
            ->give(config('notification.retry.base_delay_seconds', 60));
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
