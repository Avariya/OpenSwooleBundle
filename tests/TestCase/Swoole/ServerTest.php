<?php

declare(strict_types=1);

namespace OpenSwooleServerBundle\Tests\TestCase\Swoole;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use OpenSwoole\Atomic;
use OpenSwoole\Process;
use OpenSwoole\Runtime;
use OpenSwooleServerBundle\Batch\BatchRunner;
use OpenSwooleServerBundle\Swoole\Server;
use OpenSwooleServerBundle\Swoole\WorkerMutexPool;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Psr\Log\NullLogger;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

final class ServerTest extends TestCase
{
    public const TEST_LOG_FILE = 'test.log';

    protected function tearDown(): void
    {
        @unlink(self::TEST_LOG_FILE);
    }

    public function testHelloWorld(): void
    {
        $serverReady = new Atomic(0);
        $serverExit = new Atomic(0);

        $process = new Process(
            static function () use ($serverReady, $serverExit) {
                $server = new Server(
                    '0.0.0.0',
                    8888,
                    [
                        'enable_coroutine' => false,
                        'worker_num' => 1,
                        'pid_file' => '/tmp/openswoole_server.pid',
                        'dispatch_mode' => 3,
                    ],
                    0,
                    new class() implements HttpKernelInterface {
                        public function handle(Request $request, int $type = HttpKernelInterface::MAIN_REQUEST, bool $catch = true): Response
                        {
                            return new Response('hello world');
                        }
                    },
                    new NullLogger(),
                );

                $server->start(
                    static function (string $s) use ($serverReady) {
                        $serverReady->wakeup();
                    },
                    static function () use ($serverExit) {
                        $serverExit->wakeup();
                    },
                );
            },
        );

        $serverProcessPid = $process->start();
        $serverReady->wait();

        try {
            $output = $this->makeRequest();
        } catch (\Throwable $e) {
            $this->cleanUp($process, $serverProcessPid, $serverExit);
            $this->fail($e->getMessage());
        }

        $this->cleanUp($process, $serverProcessPid, $serverExit);

        self::assertEquals('hello world', $output);
    }

    #[DataProvider('dataProviderEnableCoroutine')]
    public function testEnableCoroutine(bool $useSyncServer, string|callable $expectedLogContent): void
    {
        $serverReady = new Atomic(0);
        $serverExit = new Atomic(0);
        $numReq = 3;

        $logger = $this->createLogger();

        $kernel = new class($logger) implements HttpKernelInterface {
            public function __construct(
                private LoggerInterface $logger,
            ) {
            }

            public function handle(
                Request $request,
                int $type = HttpKernelInterface::MAIN_REQUEST,
                bool $catch = true,
            ): Response {
                $currentReqNum = $request->query->get('req');

                $data = BatchRunner::fromCallables([
                    function () use ($currentReqNum) {
                        usleep(100000 - $currentReqNum * 10000);

                        $this->logger->debug(sprintf('callable #1 of #%d request', $currentReqNum));

                        return sprintf('callable #1 of #%d request', $currentReqNum);
                    },
                    function () use ($currentReqNum) {
                        usleep(50000 - $currentReqNum * 10000);

                        $this->logger->debug(sprintf('callable #2 of #%d request', $currentReqNum));

                        return sprintf('callable #2 of #%d request', $currentReqNum);
                    },
                ])
                    ->withHookFlags(Runtime::HOOK_SLEEP)
                    ->runAll()
                ;

                return new JsonResponse($data);
            }
        };

        $process = new Process(
            static function () use ($kernel, $serverReady, $serverExit, $useSyncServer) {
                $server = new Server(
                    '0.0.0.0',
                    8888,
                    [
                        'enable_coroutine' => true,
                        'hook_flags' => 0,
                        'worker_num' => 1,
                        'reactor_num' => 4,
                        'pid_file' => '/tmp/openswoole_server.pid',
                        'dispatch_mode' => 3,
                    ],
                    0,
                    $kernel,
                    new NullLogger(),
                    new WorkerMutexPool(),
                    $useSyncServer,
                );

                $server->start(
                    static function (string $s) use ($serverReady) {
                        $serverReady->wakeup();
                    },
                    static function () use ($serverExit) {
                        $serverExit->wakeup();
                    },
                );
            },
        );

        $serverProcessPid = $process->start();
        $serverReady->wait();

        try {
            $data = BatchRunner::fromCallables([
                function () {
                    $data = $this->makeRequest(1);

                    return json_decode($data, true);
                },
                function () {
                    $data = $this->makeRequest(2);

                    return json_decode($data, true);
                },
                function () {
                    $data = $this->makeRequest(3);

                    return json_decode($data, true);
                },
            ])->runAll();
        } catch (\Throwable $e) {
            $this->cleanUp($process, $serverProcessPid, $serverExit);
            $this->fail($e->getMessage());
        }

        $this->cleanUp($process, $serverProcessPid, $serverExit);

        self::assertEquals([
            ['callable #1 of #1 request', 'callable #2 of #1 request'],
            ['callable #1 of #2 request', 'callable #2 of #2 request'],
            ['callable #1 of #3 request', 'callable #2 of #3 request'],
        ], $data);

        $actualLogContent = file_get_contents('test.log');

        if (is_callable($expectedLogContent)) {
            $expectedLogContent($actualLogContent);
        } else {
            self::assertEquals($expectedLogContent, $actualLogContent);
        }
    }

    public static function dataProviderEnableCoroutine(): iterable
    {
        yield 'sync worker' => [
            true,
            static function (string $logContent): void {
                /**
                 * Could be something like this.
                 *
                 * callable #2 of #1 request
                 * callable #1 of #1 request
                 * callable #2 of #3 request
                 * callable #1 of #3 request
                 * callable #2 of #2 request
                 * callable #1 of #2 request
                 */
                $lines = array_flip(array_filter(array_map(trim(...), explode("\n", $logContent))));

                for ($i = 0; $i < 3; ++$i) {
                    $string = sprintf('callable #%d of #%d request', 1, $i + 1);
                    self::assertArrayHasKey($string, $lines, var_export($lines, true));
                    $idx = $lines[$string];
                    // (idx - 1) because the first callable has been ended secondly
                    self::assertSame($idx - 1, $lines[sprintf('callable #%d of #%d request', 2, $i + 1)], var_export($lines, true));
                }
            },
        ];
        yield 'async worker' => [
            false,
            <<<TEXT
        callable #2 of #3 request
        callable #2 of #2 request
        callable #2 of #1 request
        callable #1 of #3 request
        callable #1 of #2 request
        callable #1 of #1 request

        TEXT,
        ];
    }

    private function cleanUp(Process $process, int $serverProcessPid, Atomic $serverExit): void
    {
        $process->kill($serverProcessPid);

        $serverExit->wait();
    }

    private function makeRequest(int $numReq = 1): string
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'http://localhost:8888?req=' . $numReq);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($ch, CURLOPT_HEADER, 0);

        $output = curl_exec($ch);
        if ($output === false) {
            throw new \RuntimeException('CURL Error:' . curl_error($ch));
        }

        curl_close($ch);

        return $output;
    }

    private function createLogger(): LoggerInterface
    {
        $handler = new StreamHandler(self::TEST_LOG_FILE, LogLevel::DEBUG);
        $handler->setFormatter(new LineFormatter('%message%' . PHP_EOL));
        $logger = new Logger(
            'test',
            [
                $handler,
            ],
        );

        return $logger;
    }
}
