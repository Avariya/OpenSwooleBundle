<?php

declare(strict_types=1);

namespace OpenSwooleServerBundle\Swoole;

use OpenSwoole\Table;

class EventLoopLagProvider
{
    private Table $eventLoopLagHolder;

    public function __construct()
    {
        $this->eventLoopLagHolder = new Table(1);
        $this->eventLoopLagHolder->column('value', Table::TYPE_FLOAT);
        $this->eventLoopLagHolder->create();
        $this->eventLoopLagHolder->set('event_loop_lag', ['value' => .0]);
    }

    public function getEventLoopLag(): float
    {
        return $this->eventLoopLagHolder->get('event_loop_lag')['value'];
    }

    public function setEventLoopLag(float $eventLoopLag): void
    {
        $this->eventLoopLagHolder->set('event_loop_lag', ['value' => $eventLoopLag]);
    }
}
