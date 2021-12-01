<?php

namespace brown\command;

use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\Dumper;
use Nette\PhpGenerator\Helpers;
use Nette\PhpGenerator\PhpFile;
use think\console\Command;
use think\helper\Arr;
use brown\contract\rpc\ParserInterface;
use brown\rpc\client\Gateway;
use brown\rpc\JsonParser;
use function Swoole\Coroutine\run;

class RpcInterface extends Command
{
    public function configure()
    {
        $this->setName('brown:rpc:interface')
             ->setDescription('生成服务接口');
    }

    public function handle()
    {
        run(function () {
            $file = new PhpFile;
            $file->addComment('自动生成接口文件，勿删');
            $file->setStrictTypes();
            $services = [];

            $clients = $this->app->config->get('swoole.rpc.client', []);

            foreach ($clients as $name => $config) {

                $parserClass = Arr::get($config, 'parser', JsonParser::class);
                /** @var ParserInterface $parser */
                $parser = new $parserClass;

                $gateway = new Gateway($config, $parser);

                $result = $gateway->getServices();
//                print_r($result['UserInterface']);
//                print_r($result['Goods']);
//                print_r($result['GoodsService']);

                foreach ($result as $interface=>$methods){
                    $services[$name]['request@'.$interface]=[];
//                    print_r($methods);
                    foreach ($methods as $methodName =>
                    [
                         'parameters' => $parameters,
                         'returnType' => $returnType,
                         'comment' => $comment
                    ]) {

                        foreach ($parameters as $parameter){
                            $services[$name]['request@'.$interface]['method@'.$methodName]['parameter@'.$parameter['name']]=[
                                'type'=>$parameter['type'],
                                'nullable'=>'',
                                'default'=>''
                            ];



                            if (array_key_exists('default', $parameter)) {
                                $services[$name]['request@'.$interface]['method@'.$methodName]['parameter@'.$parameter['name']]['default']=$parameter['default'];
                                unset($services[$name]['request@'.$interface]['method@'.$methodName]['parameter@'.$parameter['name']]['nullable']);
                            }

                            if (array_key_exists('nullable', $parameter)) {
                                $services[$name]['request@'.$interface]['method@'.$methodName]['parameter@'.$parameter['name']]['nullable']=$parameter['nullable'];
                                unset($services[$name]['request@'.$interface]['method@'.$methodName]['parameter@'.$parameter['name']]['default']);
                            }

                        }
                    }
                }
            }
            $dumper = new Dumper();

            $services = 'return ' . $dumper->dump($services) . ';';

            file_put_contents($this->app->getBasePath() . 'rpc.php', $file . $services);

            $this->output->writeln('<info>成功生成接口文件!</info>');
        });
    }
}
