<?php

declare(strict_types=1);

namespace OpenSwooleServerBundle\Event\BatchRunner;

use OpenSwooleServerBundle\Batch\BatchRunner;
use Throwable;

final class BatchRunnerItemEndedWithException extends BatchRunnerItemEnded
{
    public function __construct(
        public readonly BatchRunner $batchRunner,
        public readonly string $key,
        public readonly Throwable $exception
    ) {
        parent::__construct($batchRunner, $key, false);
    }
}
