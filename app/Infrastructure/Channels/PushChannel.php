<?php

namespace App\Infrastructure\Channels;

use App\Domain\Channels\ChannelInterface;
use App\Domain\Channels\ChannelResult;
use App\Domain\Events\Event;
use Illuminate\Support\Facades\Log;

class PushChannel implements ChannelInterface
{
    public function getName(): string
    {
        return 'push';
    }

    public function formatMessage(Event $event): string
    {
        $template = $this->getTemplate($event->eventType);

        return str_replace(
            ['{eventType}', '{recipient}'],
            [$event->eventType, $event->recipient],
            $template
        );
    }

    public function send(Event $event, string $formattedMessage): ChannelResult
    {
        try {
            // Simulate push notification sending
            // In production, use Firebase Cloud Messaging, Apple Push Notification Service, etc.
            Log::info("Push notification sent (simulated)", [
                'to' => $event->recipient,
                'event_type' => $event->eventType,
                'message' => substr($formattedMessage, 0, 50) . '...',
            ]);

            // Simulate occasional failures for testing retry mechanism
            if (rand(1, 10) <= 2) { // 20% failure rate for testing
                return ChannelResult::failure("Push notification service temporarily unavailable");
            }

            return ChannelResult::success("Push notification sent successfully", [
                'recipient' => $event->recipient,
                'channel' => 'push',
            ]);
        } catch (\Exception $e) {
            return ChannelResult::failure($e->getMessage());
        }
    }

    private function getTemplate(string $eventType): string
    {
        $templates = [
            'USER_REGISTERED' => "Welcome! Registration successful",
            'PAYMENT_COMPLETED' => "Payment completed successfully",
            'REPORT_GENERATED' => "Your report is ready",
        ];

        return $templates[$eventType] ?? "New notification: {eventType}";
    }

}
