<?php

namespace Tests\Unit\Application;

use App\Application\Services\RoutingService;
use App\Domain\Events\Event;
use App\Domain\Events\Priority;
use App\Domain\Routing\RoutingEngineInterface;
use Tests\TestCase;

class RoutingServiceTest extends TestCase
{
    public function test_determines_channels_from_routing_engine(): void
    {
        $mockEngine = $this->createMock(RoutingEngineInterface::class);
        $mockEngine->expects($this->once())
            ->method('route')
            ->willReturn(['email', 'sms']);

        $service = new RoutingService($mockEngine);
        $event = Event::create(
            eventType: 'TEST',
            payload: [],
            recipient: 'test@example.com',
            priority: Priority::NORMAL
        );

        $channels = $service->determineChannels($event);

        $this->assertEquals(['email', 'sms'], $channels);
    }

    public function test_returns_empty_array_when_no_channels_found(): void
    {
        $mockEngine = $this->createMock(RoutingEngineInterface::class);
        $mockEngine->expects($this->once())
            ->method('route')
            ->willReturn([]);

        $service = new RoutingService($mockEngine);
        $event = Event::create(
            eventType: 'TEST',
            payload: [],
            recipient: 'test@example.com',
            priority: Priority::NORMAL
        );

        $channels = $service->determineChannels($event);

        $this->assertEquals([], $channels);
    }
}

