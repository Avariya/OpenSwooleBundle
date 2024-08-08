<?php

declare(strict_types=1);

namespace OpenSwooleServerBundle\Bridge\Monolog\Messenger;

use Monolog\Handler\AbstractHandler;
use Monolog\Handler\FormattableHandlerInterface;
use Monolog\Handler\FormattableHandlerTrait;
use Monolog\Handler\HandlerInterface;
use Monolog\Handler\ProcessableHandlerInterface;
use Monolog\Handler\ProcessableHandlerTrait;
use Monolog\LogRecord;
use Monolog\ResettableInterface;
use Symfony\Component\Messenger\MessageBusInterface;

final class SendToMessengerLogHandler extends AbstractHandler implements HandlerInterface, ProcessableHandlerInterface, ResettableInterface, FormattableHandlerInterface
{
    use FormattableHandlerTrait;
    use ProcessableHandlerTrait;

    public function __construct(
        private MessageBusInterface $messenger,
    ) {
    }

    private function beforeWriting(LogRecord $record): bool
    {
        if (!$this->isHandling($record)) {
            return false;
        }

        if (\count($this->processors) > 0) {
            $record = $this->processRecord($record);
        }

        $record->formatted = $this->getFormatter()->format($record);

        return true;
    }

    public function handle(LogRecord $record): bool
    {
        $needHandle = $this->beforeWriting($record);

        if ($needHandle) {
            $messageDTO = new LogMessageDTO([$record->formatted]);
            $this->write($messageDTO);
        }

        return $needHandle;
    }

    public function handleBatch(array $records): void
    {
        $messages = [];
        foreach ($records as $record) {
            if (!$this->beforeWriting($record)) {
                continue;
            }

            $messages[] = $record->formatted;
        }

        if (empty($messages)) {
            return;
        }

        $messageDTO = new LogMessageDTO($messages);
        $this->write($messageDTO);
    }

    private function write(LogMessageDTO $messageDTO): void
    {
        $this->messenger->dispatch($messageDTO);
    }
}
