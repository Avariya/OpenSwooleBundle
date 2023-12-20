<?php

declare(strict_types=1);

namespace OpenSwooleServerBundle\Command;

use OpenSwooleServerBundle\Swoole\Server;
use Symfony\Component\Console\Command\Command;

/**
 * Class ServerCommand
 */
abstract class ServerCommand extends Command
{
    /**
     * @var Server
     */
    protected $server;

    /**
     * @param Server      $server
     * @param string|null $name
     */
    public function __construct(Server $server, string $name = null)
    {
        $this->server = $server;
        parent::__construct($name);
    }
}
