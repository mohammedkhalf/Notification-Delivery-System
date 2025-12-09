<?php

namespace Tests\Unit\Domain;

use App\Domain\Events\Event;
use App\Domain\Events\Priority;
use Carbon\Carbon;
use Tests\TestCase;

class EventTest extends TestCase
{
    public function test_can_create_event(): void
    {
        $event = Event::create(
            eventType: 'USER_REGISTERED',
            payload: ['name' => 'John Doe'],
            recipient: 'john@example.com',
            priority: Priority::NORMAL
        );

        $this->assertEquals('USER_REGISTERED', $event->eventType);
        $this->assertEquals(['name' => 'John Doe'], $event->payload);
        $this->assertEquals('john@example.com', $event->recipient);
        $this->assertEquals(Priority::NORMAL, $event->priority);
        $this->assertNotNull($event->id);
        $this->assertInstanceOf(Carbon::class, $event->timestamp);
    }

    public function test_event_has_uuid_id(): void
    {
        $event = Event::create(
            eventType: 'TEST',
            payload: [],
            recipient: 'test@example.com',
            priority: Priority::NORMAL
        );

        $this->assertNotNull($event->id);
        $this->assertIsString($event->id);
        $this->assertEquals(36, strlen($event->id)); // UUID length
    }

    public function test_event_can_be_created_with_custom_timestamp(): void
    {
        $timestamp = Carbon::parse('2024-01-01 12:00:00');

        $event = Event::create(
            eventType: 'TEST',
            payload: [],
            recipient: 'test@example.com',
            priority: Priority::NORMAL,
            timestamp: $timestamp
        );

        $this->assertEquals($timestamp->toIso8601String(), $event->timestamp->toIso8601String());
    }

    public function test_event_to_array_returns_correct_structure(): void
    {
        $event = Event::create(
            eventType: 'USER_REGISTERED',
            payload: ['name' => 'John'],
            recipient: 'john@example.com',
            priority: Priority::HIGH
        );

        $array = $event->toArray();

        $this->assertArrayHasKey('id', $array);
        $this->assertArrayHasKey('event_type', $array);
        $this->assertArrayHasKey('payload', $array);
        $this->assertArrayHasKey('recipient', $array);
        $this->assertArrayHasKey('timestamp', $array);
        $this->assertArrayHasKey('priority', $array);
        $this->assertEquals('USER_REGISTERED', $array['event_type']);
        $this->assertEquals('high', $array['priority']);
    }

    public function test_event_uses_current_timestamp_by_default(): void
    {
        $before = Carbon::now();

        $event = Event::create(
            eventType: 'TEST',
            payload: [],
            recipient: 'test@example.com',
            priority: Priority::NORMAL
        );

        $after = Carbon::now();

        $this->assertTrue($event->timestamp->gte($before));
        $this->assertTrue($event->timestamp->lte($after));
    }
}

