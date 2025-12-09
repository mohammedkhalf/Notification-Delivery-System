<?php

namespace Tests\Unit\Application;

use App\Application\Services\EventIngestionService;
use App\Domain\Events\Event;
use App\Domain\Events\Priority;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class EventIngestionServiceTest extends TestCase
{
    private EventIngestionService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new EventIngestionService();
        Queue::fake();
    }

    public function test_can_create_event_from_array_with_camelcase(): void
    {
        $data = [
            'eventType' => 'USER_REGISTERED',
            'payload' => ['name' => 'John'],
            'recipient' => 'john@example.com',
            'priority' => 'normal',
        ];

        $event = $this->service->createEventFromArray($data);

        $this->assertEquals('USER_REGISTERED', $event->eventType);
        $this->assertEquals(['name' => 'John'], $event->payload);
        $this->assertEquals('john@example.com', $event->recipient);
        $this->assertEquals(Priority::NORMAL, $event->priority);
    }

    public function test_can_create_event_from_array_with_snake_case(): void
    {
        $data = [
            'event_type' => 'USER_REGISTERED',
            'payload' => ['name' => 'John'],
            'recipient' => 'john@example.com',
            'priority' => 'normal',
        ];

        $event = $this->service->createEventFromArray($data);

        $this->assertEquals('USER_REGISTERED', $event->eventType);
    }

    public function test_throws_exception_when_event_type_missing(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing required field: eventType');

        $this->service->createEventFromArray([
            'recipient' => 'test@example.com',
        ]);
    }

    public function test_throws_exception_when_recipient_missing(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing required field: recipient');

        $this->service->createEventFromArray([
            'eventType' => 'TEST',
        ]);
    }

    public function test_uses_default_priority_when_not_provided(): void
    {
        $data = [
            'eventType' => 'TEST',
            'recipient' => 'test@example.com',
        ];

        $event = $this->service->createEventFromArray($data);

        $this->assertEquals(Priority::NORMAL, $event->priority);
    }

    public function test_uses_empty_array_when_payload_not_provided(): void
    {
        $data = [
            'eventType' => 'TEST',
            'recipient' => 'test@example.com',
        ];

        $event = $this->service->createEventFromArray($data);

        $this->assertEquals([], $event->payload);
    }

    public function test_ingest_dispatches_job_to_queue(): void
    {
        $event = Event::create(
            eventType: 'TEST',
            payload: [],
            recipient: 'test@example.com',
            priority: Priority::NORMAL
        );

        $this->service->ingest($event);

        Queue::assertPushed(\App\Jobs\ProcessEventJob::class);
    }
}

