<?php

declare(strict_types=1);

namespace OpenSwooleServerBundle\Bridge\Monolog\Messenger;

final class LogMessageDTO
{
    /**
     * @param array<mixed> $records
     */
    public function __construct(
        public readonly array $records,
    ) {
    }
}
