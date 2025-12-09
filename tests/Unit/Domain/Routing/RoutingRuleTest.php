<?php

namespace Tests\Unit\Domain\Routing;

use App\Domain\Events\Event;
use App\Domain\Events\Priority;
use App\Domain\Routing\RoutingRule;
use Tests\TestCase;

class RoutingRuleTest extends TestCase
{
    public function test_matches_by_event_type(): void
    {
        $rule = new RoutingRule(
            eventType: 'USER_REGISTERED',
            priority: null,
            channels: ['email']
        );

        $event = Event::create(
            eventType: 'USER_REGISTERED',
            payload: [],
            recipient: 'test@example.com',
            priority: Priority::NORMAL
        );

        $this->assertTrue($rule->matches($event));
    }

    public function test_does_not_match_different_event_type(): void
    {
        $rule = new RoutingRule(
            eventType: 'USER_REGISTERED',
            priority: null,
            channels: ['email']
        );

        $event = Event::create(
            eventType: 'PAYMENT_COMPLETED',
            payload: [],
            recipient: 'test@example.com',
            priority: Priority::NORMAL
        );

        $this->assertFalse($rule->matches($event));
    }

    public function test_matches_by_priority(): void
    {
        $rule = new RoutingRule(
            eventType: null,
            priority: Priority::HIGH,
            channels: ['push']
        );

        $event = Event::create(
            eventType: 'ANY_EVENT',
            payload: [],
            recipient: 'test@example.com',
            priority: Priority::HIGH
        );

        $this->assertTrue($rule->matches($event));
    }

    public function test_matches_by_both_event_type_and_priority(): void
    {
        $rule = new RoutingRule(
            eventType: 'USER_REGISTERED',
            priority: Priority::HIGH,
            channels: ['email', 'sms']
        );

        $event = Event::create(
            eventType: 'USER_REGISTERED',
            payload: [],
            recipient: 'test@example.com',
            priority: Priority::HIGH
        );

        $this->assertTrue($rule->matches($event));
    }

    public function test_returns_channels(): void
    {
        $rule = new RoutingRule(
            eventType: null,
            priority: null,
            channels: ['email', 'sms', 'push']
        );

        $this->assertEquals(['email', 'sms', 'push'], $rule->getChannels());
    }

    public function test_matches_when_no_filters_specified(): void
    {
        $rule = new RoutingRule(
            eventType: null,
            priority: null,
            channels: ['email']
        );

        $event = Event::create(
            eventType: 'ANY_EVENT',
            payload: [],
            recipient: 'test@example.com',
            priority: Priority::LOW
        );

        $this->assertTrue($rule->matches($event));
    }
}

