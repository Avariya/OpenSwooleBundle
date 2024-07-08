<?php

declare(strict_types=1);

namespace OpenSwooleServerBundle\Tests\TestCase\Logger;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use OpenSwooleServerBundle\Batch\BatchRunner;
use OpenSwooleServerBundle\Logger\LoggerMutexDecorator;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

final class LoggerMutexDecoratorTest extends TestCase
{
    public const TEST_LOG_FILE = 'test.log';

    protected function tearDown(): void
    {
        unlink(self::TEST_LOG_FILE);
    }

    public function testWithoutCoroutine(): void
    {
        $logger = $this->createLogger();

        $logger->debug('some debug');

        self::assertSame(
            <<<TEXT
        some debug

        TEXT,
            file_get_contents(self::TEST_LOG_FILE),
        );
    }

    public function testLogCoroutine(): void
    {
        $logger = $this->createLogger();

        BatchRunner::fromCallables([
            static function () use ($logger) {
                usleep(100);
                $logger->debug('log #1');
            },
            static function () use ($logger) {
                $logger->debug('log #2');
            },
        ])->runAll();

        $logger->debug('log after runner');

        $logContent = file_get_contents(self::TEST_LOG_FILE);
        self::assertStringEndsWith("log after runner\n", $logContent);
        self::assertStringContainsString('log #1', $logContent);
        self::assertStringContainsString('log #2', $logContent);
    }

    private function createLogger(): LoggerInterface
    {
        return new Logger('test', [
            new LoggerMutexDecorator(
                (new StreamHandler(self::TEST_LOG_FILE))->setFormatter(new LineFormatter('%message%' . PHP_EOL)),
            ),
        ]);
    }
}
