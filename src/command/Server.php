<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

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

        $this->output->writeln('Starting swoole server...');

        $this->output->writeln('You can exit with <info>`CTRL-C`</info>');

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
