<?php

namespace App\Infrastructure\Channels;

use App\Domain\Channels\ChannelInterface;
use App\Domain\Channels\ChannelResult;
use App\Domain\Events\Event;
use Illuminate\Support\Facades\Log;

class EmailChannel implements ChannelInterface
{
    public function getName(): string
    {
        return 'email';
    }

    public function formatMessage(Event $event): string
    {
        $template = $this->getTemplate($event->eventType);

        return str_replace(
            ['{eventType}', '{recipient}', '{payload}'],
            [
                $event->eventType,
                $event->recipient,
                json_encode($event->payload, JSON_PRETTY_PRINT)
            ],
            $template
        );
    }

    public function send(Event $event, string $formattedMessage): ChannelResult
    {
        try {
            // Simulate email sending
            // In production, use Laravel Mail facade or a service like SendGrid, Mailgun, etc.
            Log::info("Email sent (simulated)", [
                'to' => $event->recipient,
                'event_type' => $event->eventType,
                'message' => substr($formattedMessage, 0, 100) . '...',
            ]);

            // Simulate occasional failures for testing retry mechanism
            if (rand(1, 10) <= 2) { // 20% failure rate for testing
                return ChannelResult::failure("Email service temporarily unavailable");
            }

            return ChannelResult::success("Email sent successfully", [
                'recipient' => $event->recipient,
                'channel' => 'email',
            ]);
        } catch (\Exception $e) {
            return ChannelResult::failure($e->getMessage());
        }
    }

    private function getTemplate(string $eventType): string
    {
        $templates = [
            'USER_REGISTERED' => "Welcome! You have successfully registered. Event: {eventType}\n\nDetails:\n{payload}",
            'PAYMENT_COMPLETED' => "Payment completed successfully. Event: {eventType}\n\nDetails:\n{payload}",
            'REPORT_GENERATED' => "Your report has been generated. Event: {eventType}\n\nDetails:\n{payload}",
        ];

        return $templates[$eventType] ?? "Notification: {eventType}\n\nRecipient: {recipient}\n\nDetails:\n{payload}";
    }
}
