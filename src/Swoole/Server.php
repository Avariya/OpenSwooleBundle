<?php
declare(strict_types=1);

namespace OpenSwooleServerBundle\Swoole;

use OpenSwooleServerBundle\Exception\OpenSwooleException;
use Psr\Log\LoggerInterface;
use OpenSwoole\Process;
use OpenSwooleServerBundle\Exception\SwooleException;
use Symfony\Component\HttpKernel\KernelInterface;
use Upscale\Swoole\Blackfire\Profiler;

/**
 * Class Server
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
     * @var \OpenSwoole\HTTP\Server
     */
    private $server;

    /**
     * @var KernelInterface
     */
    private $kernel;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param string          $host
     * @param int             $port
     * @param array           $options
     * @param KernelInterface $kernel
     * @param LoggerInterface $logger
     */
    public function __construct(string $host, int $port, array $options, KernelInterface $kernel, LoggerInterface $logger)
    {
        $this->host = $host;
        $this->port = $port;
        $this->options = $options;
        $this->kernel = $kernel;
        $this->logger = $logger;
    }

    /**
     * @return string
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * @param string $host
     *
     * @return $this
     */
    public function setHost(string $host): self
    {
        $this->host = $host;

        return $this;
    }

    /**
     * @return int
     */
    public function getPort(): int
    {
        return $this->port;
    }

    /**
     * @param int $port
     *
     * @return $this
     */
    public function setPort(int $port): self
    {
        $this->port = $port;

        return $this;
    }

    /**
     * Get swoole configuration option value.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function getOption(string $key)
    {
        $option = $this->options[$key];

        if (!$option) {
            throw new \InvalidArgumentException(sprintf("Parameter not found: %s", $key));
        }

        return $option;
    }

    /**
     * Start and configure swoole server.
     *
     * @param callable $cb
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
     * @return bool
     * @throws OpenSwooleException
     */
    public function stop(): bool
    {
        $kill = Process::kill($this->getPid());

        if (!$kill) {
            throw new OpenSwooleException("Swoole server not stopped!");
        }

        return $kill;
    }

    /**
     * Reload swoole server.
     *
     * @return bool
     * @throws OpenSwooleException
     */
    public function reload(): bool
    {
        $reload = Process::kill($this->getPid(), SIGUSR1);

        if (!$reload) {
            throw new OpenSwooleException("Swoole server not reloaded!");
        }

        return $reload;
    }

    /**
     * @return bool
     */
    public function isRunning(): bool
    {
        $pid = $this->getPid();

        if (!$pid) {
            return false;
        }

        Process::kill($pid, 0);

        return !swoole_errno();
    }

    /**
     * @param int $workerId
     *
     * @return bool
     */
    public function stopWorker(int $workerId = -1)
    {
        if ($workerId > -1) {
            return $this->server->stop($workerId);
        }

        return $this->server->stop();
    }

    /**
     * @return int
     */
    private function getPid(): int
    {
        $file = $this->getPidFile();

        if (!file_exists($file)) {
            return 0;
        }

        $pid = (int)file_get_contents($file);

        if (!$pid) {
            $this->removePidFile();

            return 0;
        }

        return $pid;
    }

    /**
     * Get pid file.
     *
     * @return string
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
    }

    /**
     * @param callable $cb
     */
    private function symfonyBridge(callable $cb): void
    {
        $this->server->on('start', function () use ($cb) {
            $cb('Server started!');
        });

        //request
        $this->server->on('request', function (\OpenSwoole\Http\Request $swRequest, \OpenSwoole\Http\Response $swResponse) {
            try {
                $sfRequest = Request::toSymfony($swRequest);
                $sfResponse = $this->kernel->handle($sfRequest);

                $this->kernel->terminate($sfRequest, $sfResponse);

                Response::toSwoole($swResponse, $sfResponse);
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
}
