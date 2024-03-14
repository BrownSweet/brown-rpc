<?php
/**
 *   Author:Brown
 *   Email: 455764041@qq.com
 *   Time: 2022/10/16 12:52
 */

namespace brown\command;




use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class RpcDoc extends Command
{

    public function configure()
    {
        $this->setName('rpcdoc:load')
            ->setDescription('生成rpc server接口')
            ->addArgument('services',null,'选择要生成接口文档的服务');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $output->write('生成中...');
        (new RpcDocGenerateor())->generateor( $input,$output);

        return 1;

    }
}