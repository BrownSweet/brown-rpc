<?php

namespace brown\command;

use think\console\Command;
use brown\Manager;

class Server extends Command
{
    public function configure()
    {
        $this->setName('brown:rpc')
             ->setDescription('启动swoole服务');
    }

    public function handle(Manager $manager)
    {
        $this->checkEnvironment();

        $this->output->writeln('启动 swoole 服务');

        $this->output->writeln('按下 <info>`CTRL-C`</info>停止服务');

        $manager->start();
    }

    /**
     * 检查环境
     */
    protected function checkEnvironment()
    {
        if (!extension_loaded('swoole')) {
            $this->output->error('Can\'t detect Swoole extension installed.');

            exit(1);
        }

        if (!version_compare(swoole_version(), '4.4.8', 'ge')) {
            $this->output->error('Your Swoole version must be higher than `4.4.8`.');

            exit(1);
        }
    }

}
