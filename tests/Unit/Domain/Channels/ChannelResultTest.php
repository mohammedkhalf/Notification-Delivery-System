<?php

namespace Tests\Unit\Domain\Channels;

use App\Domain\Channels\ChannelResult;
use Tests\TestCase;

class ChannelResultTest extends TestCase
{
    public function test_can_create_success_result(): void
    {
        $result = ChannelResult::success('Message sent');

        $this->assertTrue($result->success);
        $this->assertEquals('Message sent', $result->message);
        $this->assertNull($result->error);
    }

    public function test_can_create_failure_result(): void
    {
        $result = ChannelResult::failure('Connection timeout');

        $this->assertFalse($result->success);
        $this->assertEquals('Connection timeout', $result->error);
        $this->assertNull($result->message);
    }

    public function test_can_include_metadata(): void
    {
        $metadata = ['recipient' => 'test@example.com', 'message_id' => '123'];
        $result = ChannelResult::success('Sent', $metadata);

        $this->assertEquals($metadata, $result->metadata);
    }

    public function test_failure_can_include_metadata(): void
    {
        $metadata = ['error_code' => 'TIMEOUT', 'retry_after' => 60];
        $result = ChannelResult::failure('Failed', $metadata);

        $this->assertEquals($metadata, $result->metadata);
    }
}

