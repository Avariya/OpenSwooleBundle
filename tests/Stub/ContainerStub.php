<?php

declare(strict_types=1);

namespace OpenSwooleServerBundle\Tests\Stub;

use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

final class ContainerStub implements ContainerInterface
{
    public function __construct(
        private array $services = [],
    ) {
    }

    public function get(string $id): mixed
    {
        if (!isset($this->services[$id])) {
            throw new NotFoundExceptionInterface(sprintf('Service %s not found', $id));
        }

        return $this->services[$id];
    }

    public function has(string $id): bool
    {
        return isset($this->services[$id]);
    }

    public function set(string $id, mixed $service): void
    {
        $this->services[$id] = $service;
    }
}
