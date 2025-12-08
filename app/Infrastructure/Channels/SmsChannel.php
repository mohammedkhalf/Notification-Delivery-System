<?php

namespace App\Infrastructure\Channels;

use App\Domain\Channels\ChannelInterface;
use App\Domain\Channels\ChannelResult;
use App\Domain\Events\Event;
use Illuminate\Support\Facades\Log;

class SmsChannel implements ChannelInterface
{
    public function getName(): string
    {
        return 'sms';
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
            // Simulate SMS sending
            // In production, use a service like Twilio, AWS SNS, etc.
            Log::info("SMS sent (simulated)", [
                'to' => $event->recipient,
                'event_type' => $event->eventType,
                'message' => substr($formattedMessage, 0, 50) . '...',
            ]);

            // Simulate occasional failures for testing retry mechanism
            if (rand(1, 10) <= 2) { // 20% failure rate for testing
                return ChannelResult::failure("SMS service temporarily unavailable");
            }

            return ChannelResult::success("SMS sent successfully", [
                'recipient' => $event->recipient,
                'channel' => 'sms',
            ]);
        } catch (\Exception $e) {
            return ChannelResult::failure($e->getMessage());
        }
    }

    private function getTemplate(string $eventType): string
    {
        $templates = [
            'USER_REGISTERED' => "Welcome! Registration successful. Event: {eventType}",
            'PAYMENT_COMPLETED' => "Payment completed. Event: {eventType}",
            'REPORT_GENERATED' => "Report ready. Event: {eventType}",
        ];

        return $templates[$eventType] ?? "Notification: {eventType}";
    }

}
