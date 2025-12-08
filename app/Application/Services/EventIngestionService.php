<?php

namespace App\Application\Services;

use App\Domain\Events\Event;
use App\Domain\Events\Priority;
use App\Jobs\ProcessEventJob;

class EventIngestionService
{
    public function ingest(Event $event): void
    {
        // Dispatch to queue for async processing
        ProcessEventJob::dispatch($event->toArray());
    }

    public function createEventFromArray(array $data): Event
    {
        $eventType = $data['eventType'] ?? $data['event_type'] ?? null;
        $recipient = $data['recipient'] ?? null;
        $payload = $data['payload'] ?? [];
        $priority = $data['priority'] ?? 'normal';
        $timestamp = $data['timestamp'] ?? null;
        $id = $data['id'] ?? null;

        if ($eventType === null) {
            throw new \InvalidArgumentException("Missing required field: eventType");
        }

        if ($recipient === null) {
            throw new \InvalidArgumentException("Missing required field: recipient");
        }

        return Event::create(
            eventType: $eventType,
            payload: $payload,
            recipient: $recipient,
            priority: Priority::from($priority),
            timestamp: $timestamp ? \Carbon\Carbon::parse($timestamp) : null,
            id: $id
        );
    }

}
