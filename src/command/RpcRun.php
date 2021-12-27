<?php
/**
 *   Author:Brown
 *   Email: 455764041@qq.com
 *   Time: 2021/12/27 22:06
 */

namespace brown\command;

use brown\Manager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RpcRun extends Command
{
    public function configure()
    {
        $this->setName('rpc:run')
            ->setDescription('启动swoole服务');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $output->write('服务开启');
        (new Manager())->start();
    }
}