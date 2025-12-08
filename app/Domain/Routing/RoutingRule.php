<?php


namespace App\Domain\Routing;

use App\Domain\Events\Event;
use App\Domain\Events\Priority;

class RoutingRule
{
    public function __construct(
        public readonly ?string   $eventType = null,
        public readonly ?Priority $priority = null,
        public readonly array     $channels = []
    )
    {
    }

    public function matches(Event $event): bool
    {
        if ($this->eventType !== null && $event->eventType !== $this->eventType) {
            return false;
        }

        if ($this->priority !== null && $event->priority !== $this->priority) {
            return false;
        }

        return true;
    }

    public function getChannels(): array
    {
        return $this->channels;
    }
}

