<?php

declare(strict_types=1);

namespace OpenSwooleServerBundle\Bridge\Monolog\Messenger;

final class LogDTO
{
    public function __construct(
        public readonly mixed $data,
    ) {
    }
}
