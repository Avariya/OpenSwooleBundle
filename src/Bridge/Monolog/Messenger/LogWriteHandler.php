<?php

declare(strict_types=1);

namespace OpenSwooleServerBundle\Bridge\Monolog\Messenger;

final class LogWriteHandler
{
    /**
     * @param iterable<LogWriterInterface> $writers
     */
    public function __construct(
        private iterable $writers,
    ) {
    }

    public function __invoke(LogMessageDTO $dto): void
    {
        foreach ($this->writers as $writer) {
            $writer->write($dto);
        }
    }
}
