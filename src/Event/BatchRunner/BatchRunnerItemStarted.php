<?php

declare(strict_types=1);

namespace OpenSwooleServerBundle\Event\BatchRunner;

use OpenSwooleServerBundle\Batch\BatchRunner;
use OpenSwooleServerBundle\Event\OpenSwooleEvent;

final class BatchRunnerItemStarted extends OpenSwooleEvent
{
    public function __construct(
        public readonly BatchRunner $batchRunner,
        public readonly string $key,
    ) {
    }
}
