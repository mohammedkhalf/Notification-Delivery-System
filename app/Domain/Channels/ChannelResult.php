<?php


namespace App\Domain\Channels;

class ChannelResult
{
    public function __construct(
        public readonly bool    $success,
        public readonly ?string $message = null,
        public readonly ?string $error = null,
        public readonly ?array  $metadata = null
    )
    {}

    public static function success(?string $message = null, ?array $metadata = null): self
    {
        return new self(
            success: true,
            message: $message,
            metadata: $metadata
        );
    }

    public static function failure(string $error, ?array $metadata = null): self
    {
        return new self(
            success: false,
            error: $error,
            metadata: $metadata
        );
    }
}

