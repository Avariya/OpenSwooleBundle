<?php

declare(strict_types=1);

namespace OpenSwooleServerBundle\Bridge\Monolog\Messenger;

interface LogWriterInterface
{
    public function write(LogMessageDTO $dto): void;
}
