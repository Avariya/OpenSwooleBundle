<?php

declare(strict_types=1);

namespace OpenSwooleServerBundle\Swoole;

use OpenSwoole\Process;
use OpenSwoole\Runtime;
use OpenSwoole\Util;
use OpenSwooleServerBundle\Exception\OpenSwooleException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\HttpKernel\TerminableInterface;
use Upscale\Swoole\Blackfire\Profiler;

/**
 * Class Server.
 */
class Server
{
    /**
     * @var string
     */
    private $host;

    /**
     * @var int
     */
    private $port;

    /**
     * @var array
     */
    private $options;

    /**
     * @var int
     */
    private $hookFlags;

    /**
     * @var \OpenSwoole\HTTP\Server
     */
    private $server;

    /**
     * @var KernelInterface&TerminableInterface
     */
    private $kernel;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(string $host, int $port, array $options, int $hookFlags, KernelInterface $kernel, LoggerInterface $logger)
    {
        $this->host = $host;
        $this->port = $port;
        $this->options = $options;
        $this->hookFlags = $hookFlags;
        $this->kernel = $kernel;
        $this->logger = $logger;
    }

    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * @return $this
     */
    public function setHost(string $host): self
    {
        $this->host = $host;

        return $this;
    }

    public function getPort(): int
    {
        return $this->port;
    }

    /**
     * @return $this
     */
    public function setPort(int $port): self
    {
        $this->port = $port;

        return $this;
    }

    /**
     * Get swoole configuration option value.
     */
    public function getOption(string $key)
    {
        $option = $this->options[$key];

        if (!$option) {
            throw new \InvalidArgumentException(sprintf('Parameter not found: %s', $key));
        }

        return $option;
    }

    /**
     * Start and configure swoole server.
     */
    public function start(callable $cb): void
    {
        $this->createServer();
        $this->configureSwooleServer();
        $this->symfonyBridge($cb);
    }

    /**
     * Stop the swoole server.
     *
     * @throws OpenSwooleException
     */
    public function stop(): bool
    {
        $kill = Process::kill($this->getPid());

        if (!$kill) {
            throw new OpenSwooleException('Swoole server not stopped!');
        }

        return $kill;
    }

    /**
     * Reload swoole server.
     *
     * @throws OpenSwooleException
     */
    public function reload(): bool
    {
        $reload = Process::kill($this->getPid(), SIGUSR1);

        if (!$reload) {
            throw new OpenSwooleException('Swoole server not reloaded!');
        }

        return $reload;
    }

    public function isRunning(): bool
    {
        $pid = $this->getPid();

        if (!$pid) {
            return false;
        }

        Process::kill($pid, 0);

        return !Util::getLastErrorCode();
    }

    /**
     * @return bool
     */
    public function stopWorker(int $workerId = -1)
    {
        if ($workerId > -1) {
            return $this->server->stop($workerId);
        }

        return $this->server->stop();
    }

    private function getPid(): int
    {
        $file = $this->getPidFile();

        if (!file_exists($file)) {
            return 0;
        }

        $pid = (int) file_get_contents($file);

        if (!$pid) {
            $this->removePidFile();

            return 0;
        }

        return $pid;
    }

    /**
     * Get pid file.
     */
    private function getPidFile(): string
    {
        return $this->getOption('pid_file');
    }

    /**
     * Remove the pid file.
     */
    private function removePidFile(): void
    {
        $file = $this->getPidFile();

        if (file_exists($file)) {
            unlink($file);
        }
    }

    /**
     * Create the swoole http server.
     */
    private function createServer(): void
    {
        $this->server = new \OpenSwoole\HTTP\Server($this->host, $this->port);
    }

    /**
     * Configure the created server.
     */
    private function configureSwooleServer(): void
    {
        $this->server->set($this->options);
        Runtime::enableCoroutine($this->getOption('enable_coroutine'), $this->hookFlags);
    }

    private function symfonyBridge(callable $cb): void
    {
        $this->server->on('start', static function () use ($cb) {
            $cb('Server started!');
        });

        // request
        $this->server->on('request', function (\OpenSwoole\Http\Request $swRequest, \OpenSwoole\Http\Response $swResponse) {
            try {
                $sfRequest = Request::toSymfony($swRequest);
                $sfResponse = $this->kernel->handle($sfRequest);

                Response::toSwoole($swResponse, $sfResponse);

                if ($this->kernel instanceof TerminableInterface) {
                    $this->kernel->terminate($sfRequest, $sfResponse);
                }
            } catch (\Throwable $throwable) {
                $this->logger->error($throwable->getMessage(), [
                    'class' => get_class($throwable),
                    'file' => $throwable->getFile(),
                    'line' => $throwable->getLine(),
                    'trace' => $throwable->getTrace(),
                ]);

                $swResponse->status(500);
                $swResponse->end(json_encode([
                    'code' => 500,
                    'message' => $throwable->getMessage(),
                ]));
            }
        });

        $profiler = new Profiler();
        $profiler->instrument($this->server);

        $this->server->start();
    }

    public function stats(int $mode = 0)
    {
        return $this->server->stats($mode);
    }
}
