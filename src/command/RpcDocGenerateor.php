<?php
/**
 *   Author:Brown
 *   Email: 455764041@qq.com
 *   Time: 2022/10/16 13:18
 */

namespace brown\command;



use brown\RpcClient;
use brown\server\core\Application;
use Nette\PhpGenerator\ClassType;

use Nette\PhpGenerator\Helpers;
use Nette\PhpGenerator\PhpFile;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class RpcDocGenerateor
{

    use Application;
    public function generateor(InputInterface $input, OutputInterface $output){
        $services=$this->getConfig('rpc.client.register.service_name');

        foreach ($services as $service){
            mkdir($this->getRootPath().'app/rpc/'.strtolower($service));
            $class=(new RpcClient())->Service($service)->request('rpc_doc')->rpc_doc([]);
            $result=$class;
            $output->writeln('创建目录...');

            $output->writeln('正在生成命名空间...');
            foreach ($result as $interface => $methods) {
//                print_r($interface);
                $file = new PhpFile;
                $file->addComment('// +----------------------------------------------------------------------
// | Brown-Rpc 
// +----------------------------------------------------------------------
// | 自动生成接口调用类
// +----------------------------------------------------------------------
// | Author: tianyu <455764041@qq.com>
// +----------------------------------------------------------------------
             ');
                $file->setStrictTypes();
                $namespace = $file->addNamespace("app\\rpc\\".strtolower($service));
                $class = $namespace->addClass(ucfirst($service).ucfirst($interface));
                $output->write('正在创建接口'.ucfirst($service).ucfirst($interface));
                echo PHP_EOL;
                $namespace->addUse('brown\\RpcClient');
                $output->writeln('正在加载'.'brown\\RpcClient');
                foreach ($methods as $methodName => ['parameters' => $parameters, 'returnType' => $returnType, 'comment' => $comment]) {
                    $method = $class->addMethod($methodName)
                        ->setVisibility(ClassType::VISIBILITY_PUBLIC)
                        ->setComment(Helpers::unformatDocComment($comment))
                        ->setReturnType($returnType);
                    $output->writeln("正在创建接口".$methodName);
                    $codeLeft="return (new RpcClient())->Service('$service')->request('$interface')->$methodName([";

                    foreach ($parameters as $parameter) {

                        $name=$parameter['name'];
                        $codeRight.="'$name'=>$".$parameter['name'].",";
                        if ($parameter['type'] && (class_exists($parameter['type']) || interface_exists($parameter['type']))) {
                            $namespace->addUse($parameter['type']);
                        }
                        $param = $method->addParameter($parameter['name'])
                            ->setType($parameter['type']);
                        $output->writeln("正在为接口添加参数".$parameter['name']);
                        if (array_key_exists('default', $parameter)) {
                            $param->setDefaultValue($parameter['default']);
                        }
                        $output->writeln("正在为接口添加参数".$parameter['name'].'添加默认值'.$parameter['default']);
                        if (array_key_exists('nullable', $parameter)) {
                            $param->setNullable();
                        }

                    }
                    $method->addBody($codeLeft.$codeRight."]);");
                    $output->writeln("正在为接口添加返回值".$parameter['name'].$codeLeft.$codeRight."]);");
                    $codeRight='';
                }
                $codeLeft='';
                file_put_contents($this->getRootPath().'app/rpc/'.strtolower($service) . ucfirst($service).ucfirst($interface).'.php', $file);//
                $output->writeln("创建成功".ucfirst($service).ucfirst($interface).'.php');
                $output->writeln("直接实例化调用 (new ".ucfirst($service).ucfirst($interface).'())');
            }

        }
    }
}