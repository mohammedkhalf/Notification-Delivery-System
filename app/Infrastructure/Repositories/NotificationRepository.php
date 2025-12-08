<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Events\Event;
use App\Domain\Events\Priority;
use App\Domain\Notifications\Notification;
use App\Domain\Notifications\NotificationStatus;
use App\Domain\Repositories\NotificationRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class NotificationRepository implements NotificationRepositoryInterface
{
    public function create(Event $event, string $channel, NotificationStatus $status): Notification
    {
        $eventId = $event->id;

        // Save event if not already saved
        $eventExists = DB::table('events')->where('id', $eventId)->exists();
        if (!$eventExists) {
            DB::table('events')->insert([
                'id' => $eventId,
                'event_type' => $event->eventType,
                'payload' => json_encode($event->payload),
                'recipient' => $event->recipient,
                'priority' => $event->priority->value,
                'timestamp' => $event->timestamp,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $notificationId = (string) Str::uuid();

        DB::table('notifications')->insert([
            'id' => $notificationId,
            'event_id' => $eventId,
            'channel' => $channel,
            'status' => $status->value,
            'attempt_count' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $this->findById($notificationId);
    }

    public function save(Notification $notification): void
    {
        DB::table('notifications')
            ->where('id', $notification->id)
            ->update([
                'status' => $notification->status->value,
                'attempt_count' => $notification->attemptCount,
                'last_attempt_at' => $notification->lastAttemptAt,
                'last_failure_reason' => $notification->lastFailureReason,
                'delivered_at' => $notification->deliveredAt,
                'updated_at' => now(),
            ]);

        // If moved to dead letter, also save to dead_letter_notifications table
        if ($notification->status === NotificationStatus::DEAD_LETTER) {
            $this->saveToDeadLetter($notification);
        }
    }

    private function saveToDeadLetter(Notification $notification): void
    {
        // Check if already exists
        $exists = DB::table('dead_letter_notifications')
            ->where('notification_id', $notification->id)
            ->exists();

        if (!$exists) {
            DB::table('dead_letter_notifications')->insert([
                'id' => (string) Str::uuid(),
                'notification_id' => $notification->id,
                'event_id' => $notification->event->id,
                'channel' => $notification->channel,
                'attempt_count' => $notification->attemptCount,
                'last_failure_reason' => $notification->lastFailureReason,
                'event_data' => json_encode($notification->event->toArray()),
                'notification_data' => json_encode([
                    'id' => $notification->id,
                    'channel' => $notification->channel,
                    'status' => $notification->status->value,
                    'attempt_count' => $notification->attemptCount,
                    'last_failure_reason' => $notification->lastFailureReason,
                ]),
                'moved_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function findById(string $id): ?Notification
    {
        $data = DB::table('notifications')
            ->join('events', 'notifications.event_id', '=', 'events.id')
            ->where('notifications.id', $id)
            ->first();

        if (!$data) {
            return null;
        }

        return $this->hydrate($data);
    }

    public function findByEventId(string $eventId): array
    {
        $data = DB::table('notifications')
            ->join('events', 'notifications.event_id', '=', 'events.id')
            ->where('events.id', $eventId)
            ->get();

        return $data->map(fn($row) => $this->hydrate($row))->toArray();
    }

    public function findFailed(int $limit = 50): array
    {
        $data = DB::table('notifications')
            ->join('events', 'notifications.event_id', '=', 'events.id')
            ->where('notifications.status', NotificationStatus::FAILED->value)
            ->orderBy('notifications.created_at', 'desc')
            ->limit($limit)
            ->get();

        return $data->map(fn($row) => $this->hydrate($row))->toArray();
    }

    public function findDeadLetter(int $limit = 50): array
    {
        $data = DB::table('notifications')
            ->join('events', 'notifications.event_id', '=', 'events.id')
            ->where('notifications.status', NotificationStatus::DEAD_LETTER->value)
            ->orderBy('notifications.created_at', 'desc')
            ->limit($limit)
            ->get();

        return $data->map(fn($row) => $this->hydrate($row))->toArray();
    }

    public function findPendingRetries(int $maxAttempts, int $limit = 50): array
    {
        $data = DB::table('notifications')
            ->join('events', 'notifications.event_id', '=', 'events.id')
            ->where('notifications.status', NotificationStatus::FAILED->value)
            ->where('notifications.attempt_count', '<', $maxAttempts)
            ->orderBy('notifications.last_attempt_at', 'asc')
            ->limit($limit)
            ->get();

        return $data->map(fn($row) => $this->hydrate($row))->toArray();
    }

    private function hydrate($data): Notification
    {
        $event = new Event(
            eventType: $data->event_type,
            payload: json_decode($data->payload, true),
            recipient: $data->recipient,
            timestamp: \Carbon\Carbon::parse($data->timestamp),
            priority: Priority::from($data->priority),
            id: $data->event_id
        );

        return new Notification(
            id: $data->id,
            event: $event,
            channel: $data->channel,
            status: NotificationStatus::from($data->status),
            createdAt: \Carbon\Carbon::parse($data->created_at),
            deliveredAt: $data->delivered_at ? \Carbon\Carbon::parse($data->delivered_at) : null,
            attemptCount: $data->attempt_count,
            lastAttemptAt: $data->last_attempt_at ? \Carbon\Carbon::parse($data->last_attempt_at) : null,
            lastFailureReason: $data->last_failure_reason
        );
    }


}
