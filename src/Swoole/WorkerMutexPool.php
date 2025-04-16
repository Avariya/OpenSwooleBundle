<?php

declare(strict_types=1);

namespace OpenSwooleServerBundle\Swoole;

use OpenSwooleServerBundle\Swoole\Mutex\AtomicMutex;
use OpenSwooleServerBundle\Swoole\Mutex\MutexFactory;
use OpenSwooleServerBundle\Swoole\Mutex\MutexInterface;

final class WorkerMutexPool
{
    private AtomicMutex $mutex;

    /**
     * @param MutexInterface[] $pool
     */
    public function __construct(
        private array $pool = [],
    ) {
        $this->mutex = MutexFactory::createBetweenProcesses();
    }

    public function getOrCreate(string|int $workerId): MutexInterface
    {
        $this->mutex->lock();
        $chan = $this->pool[$workerId] ??= MutexFactory::createByCoroutineContext();
        $this->mutex->unlock();

        return $chan;
    }

    public function create(string|int $workerId): MutexInterface
    {
        $this->mutex->lock();
        $chan = MutexFactory::createByCoroutineContext();
        $this->pool[$workerId] = $chan;
        $this->mutex->unlock();

        return $chan;
    }
}
