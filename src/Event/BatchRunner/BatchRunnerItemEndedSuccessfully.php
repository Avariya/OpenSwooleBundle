<?php

declare(strict_types=1);

namespace OpenSwooleServerBundle\Event\BatchRunner;

use OpenSwooleServerBundle\Batch\BatchRunner;

final class BatchRunnerItemEndedSuccessfully extends BatchRunnerItemEnded
{
    public function __construct(
        public readonly BatchRunner $batchRunner,
        public readonly string $key,
    ) {
        parent::__construct($this->batchRunner, $this->key, true);
    }
}
