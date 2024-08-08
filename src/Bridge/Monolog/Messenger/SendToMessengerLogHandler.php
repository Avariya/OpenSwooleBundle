<?php

declare(strict_types=1);

namespace OpenSwooleServerBundle\Bridge\Monolog\Messenger;

use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Handler\FormattableHandlerInterface;
use Monolog\Handler\HandlerInterface;
use Monolog\Handler\ProcessableHandlerInterface;
use Monolog\LogRecord;
use Monolog\ResettableInterface;
use Symfony\Component\Messenger\MessageBusInterface;

final class SendToMessengerLogHandler extends AbstractProcessingHandler implements HandlerInterface, ProcessableHandlerInterface, ResettableInterface, FormattableHandlerInterface
{
    public function __construct(
        private MessageBusInterface $messenger,
    ) {
    }

    protected function write(LogRecord $record): void
    {
        $dto = new LogDTO($record->formatted);

        $this->messenger->dispatch($dto);
    }
}
