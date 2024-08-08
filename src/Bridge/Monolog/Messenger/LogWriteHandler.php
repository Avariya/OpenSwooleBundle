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

    public function __invoke(LogDTO $logDTO): void
    {
        foreach ($this->writers as $writer) {
            $writer->write($logDTO);
        }
    }
}
