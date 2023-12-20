<?php

declare(strict_types=1);

namespace OpenSwooleServerBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class ReloadCommand
 */
class ReloadCommand extends ServerCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('openswoole:server:reload')
            ->setDescription('Reload OpenSwoole HTTP Server.');
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
            if ($this->server->isRunning()) {
                $this->server->reload();
                $style->success('OpenSwoole server reloaded!');
            } else {
                $style->warning('Server not running! Please before start the server.');
            }
        } catch (\Exception $exception) {
            $style->error($exception->getMessage());

            return 1;
        }

        return 0;
    }
}