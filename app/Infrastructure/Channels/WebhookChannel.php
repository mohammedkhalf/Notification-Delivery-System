<?php

namespace App\Infrastructure\Channels;

use App\Domain\Channels\ChannelInterface;
use App\Domain\Channels\ChannelResult;
use App\Domain\Events\Event;
use Illuminate\Support\Facades\Log;

class WebhookChannel implements ChannelInterface
{
    public function getName(): string
    {
        return 'webhook';
    }

    public function formatMessage(Event $event): string
    {
        return json_encode([
            'event_type' => $event->eventType,
            'recipient' => $event->recipient,
            'payload' => $event->payload,
            'priority' => $event->priority->value,
            'timestamp' => $event->timestamp->toIso8601String(),
        ], JSON_PRETTY_PRINT);
    }

    public function send(Event $event, string $formattedMessage): ChannelResult
    {
        try {
            // Get webhook URL from config or event payload
            $webhookUrl = $event->payload['webhook_url'] ?? config('notification.webhook.default_url');

            if (!$webhookUrl) {
                return ChannelResult::failure("Webhook URL not configured");
            }

            // Simulate webhook call
            // In production, make actual HTTP request
            Log::info("Webhook called (simulated)", [
                'url' => $webhookUrl,
                'event_type' => $event->eventType,
                'payload' => json_decode($formattedMessage, true),
            ]);

            // Simulate occasional failures for testing retry mechanism
            if (rand(1, 10) <= 2) { // 20% failure rate for testing
                return ChannelResult::failure("Webhook endpoint temporarily unavailable");
            }

            return ChannelResult::success("Webhook called successfully", [
                'url' => $webhookUrl,
                'channel' => 'webhook',
            ]);
        } catch (\Exception $e) {
            return ChannelResult::failure($e->getMessage());
        }
    }

}
