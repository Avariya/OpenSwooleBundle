<?php

declare(strict_types=1);

namespace OpenSwooleServerBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class StatusCommand
 */
class StatusCommand extends ServerCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('openswoole:server:status')
            ->setDescription('Status OpenSwoole HTTP Server.');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $style = new SymfonyStyle($input, $output);
        try {
            $style->table(
                ['Host', 'Port', 'Status'],
                [[$this->server->getHost(), $this->server->getPort(), $this->server->isRunning() ? 'Running' : 'Stopped']]
            );
        } catch (\Exception $exception) {
            $style->error($exception->getMessage());

            return 1;
        }

        return 0;
    }
}
