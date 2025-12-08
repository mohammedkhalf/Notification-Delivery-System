<?php

namespace App\Infrastructure\Routing;

use App\Domain\Events\Event;
use App\Domain\Events\Priority;
use App\Domain\Routing\RoutingEngineInterface;
use App\Domain\Routing\RoutingRule;
use Illuminate\Support\Facades\Config;

class ConfigBasedRoutingEngine implements RoutingEngineInterface
{
    private array $rules = [];

    public function __construct()
    {
        $this->loadRules();
    }

    public function route(Event $event): array
    {
        $channels = [];

        foreach ($this->rules as $rule) {
            if ($rule->matches($event)) {
                $channels = array_merge($channels, $rule->getChannels());
            }
        }

        // Remove duplicates and return
        return array_unique($channels);
    }

    private function loadRules(): void
    {
        $configRules = Config::get('notification.routing_rules', []);

        foreach ($configRules as $configRule) {
            $this->rules[] = new RoutingRule(
                eventType: $configRule['event_type'] ?? null,
                priority: isset($configRule['priority']) ? Priority::from($configRule['priority']) : null,
                channels: $configRule['channels'] ?? []
            );
        }
    }
}
