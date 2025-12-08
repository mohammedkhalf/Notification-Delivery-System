<?php

namespace App\Domain\Channels;

use App\Domain\Events\Event;

interface ChannelInterface
{
    /**
     * Get the channel name
     */
    public function getName(): string;

    /**
     * Format the message from the event
     */
    public function formatMessage(Event $event): string;

    /**
     * Send the notification
     *
     * @return ChannelResult
     */
    public function send(Event $event, string $formattedMessage): ChannelResult;
}

