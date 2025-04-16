<?php

declare(strict_types=1);

namespace OpenSwooleServerBundle\Tests\Messenger;

final class TestMessage
{
    public function __construct(
        public readonly string $message,
    ) {
    }
}
