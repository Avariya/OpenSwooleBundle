<?php

declare(strict_types=1);

namespace OpenSwooleServerBundle\Swoole\Mutex;

use OpenSwooleServerBundle\Swoole\CoroutineHelper;

final class MutexFactory
{
    public static function createByCoroutineContext(): MutexInterface
    {
        if (CoroutineHelper::inCoroutine()) {
            return new ChannelMutex();
        }

        return new NoopMutex();
    }

    public static function createBetweenProcesses(): MutexInterface
    {
        return new AtomicMutex();
    }
}
