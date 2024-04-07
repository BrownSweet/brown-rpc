<?php

namespace brown\command;

use brown\Manager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ServerRun extends Command
{
    public function configure()
    {
        $this->setName('server:run')
            ->setDescription('启动swoole服务');
    }

    public function execute(InputInterface $input, OutputInterface $output):int
    {
        $output->write('服务开启');
        (new Manager())->start();
        return 1;
    }
}