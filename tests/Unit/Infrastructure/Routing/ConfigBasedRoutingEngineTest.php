<?php

namespace Tests\Unit\Infrastructure\Routing;

use App\Domain\Events\Event;
use App\Domain\Events\Priority;
use App\Infrastructure\Routing\ConfigBasedRoutingEngine;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class ConfigBasedRoutingEngineTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Set test routing rules
        Config::set('notification.routing_rules', [
            [
                'event_type' => 'USER_REGISTERED',
                'priority' => null,
                'channels' => ['email', 'sms'],
            ],
            [
                'event_type' => null,
                'priority' => 'high',
                'channels' => ['push', 'email'],
            ],
        ]);
    }

    public function test_routes_by_event_type(): void
    {
        $engine = new ConfigBasedRoutingEngine();

        $event = Event::create(
            eventType: 'USER_REGISTERED',
            payload: [],
            recipient: 'test@example.com',
            priority: Priority::NORMAL
        );

        $channels = $engine->route($event);

        $this->assertContains('email', $channels);
        $this->assertContains('sms', $channels);
        $this->assertCount(2, $channels);
    }

    public function test_routes_by_priority(): void
    {
        $engine = new ConfigBasedRoutingEngine();

        $event = Event::create(
            eventType: 'ANY_EVENT',
            payload: [],
            recipient: 'test@example.com',
            priority: Priority::HIGH
        );

        $channels = $engine->route($event);

        $this->assertContains('push', $channels);
        $this->assertContains('email', $channels);
        $this->assertCount(2, $channels);
    }

    public function test_returns_empty_array_when_no_rules_match(): void
    {
        $engine = new ConfigBasedRoutingEngine();

        $event = Event::create(
            eventType: 'UNKNOWN_EVENT',
            payload: [],
            recipient: 'test@example.com',
            priority: Priority::LOW
        );

        $channels = $engine->route($event);

        $this->assertEquals([], $channels);
    }

    public function test_removes_duplicate_channels(): void
    {
        Config::set('notification.routing_rules', [
            [
                'event_type' => 'TEST',
                'priority' => null,
                'channels' => ['email', 'sms'],
            ],
            [
                'event_type' => null,
                'priority' => 'normal',
                'channels' => ['email', 'push'],
            ],
        ]);

        $engine = new ConfigBasedRoutingEngine();

        $event = Event::create(
            eventType: 'TEST',
            payload: [],
            recipient: 'test@example.com',
            priority: Priority::NORMAL
        );

        $channels = $engine->route($event);

        // Should have email, sms, push (no duplicates)
        $this->assertCount(3, $channels);
        $this->assertEquals(['email', 'sms', 'push'], array_values(array_unique($channels)));
    }
}

