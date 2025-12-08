<?php

namespace App\Domain\Routing;

use App\Domain\Events\Event;
interface RoutingEngineInterface
{
    /**
     * Determine which channels should receive this event
     *
     * @return string[] Array of channel names
     */
    public function route(Event $event): array;
}
