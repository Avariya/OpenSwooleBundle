<?php

declare(strict_types=1);

namespace OpenSwooleServerBundle\Swoole\Mutex;

use OpenSwoole\Coroutine\Channel;

final class ChannelMutex implements MutexInterface
{
    private Channel $channel;

    public function __construct()
    {
        $this->channel = new Channel(1);
    }

    public function lock(): void
    {
        $this->channel->push(true);
    }

    public function unlock(): void
    {
        $this->channel->pop();
    }
}
