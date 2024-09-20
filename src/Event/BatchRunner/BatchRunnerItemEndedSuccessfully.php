<?php

declare(strict_types=1);

namespace OpenSwooleServerBundle\Event\BatchRunner;

use OpenSwooleServerBundle\Batch\BatchRunner;

final class BatchRunnerItemEndedSuccessfully extends BatchRunnerItemEnded
{
    public function __construct(
        BatchRunner $batchRunner,
        string $key,
    ) {
        parent::__construct($batchRunner, $key, true);
    }
}
