<?php
namespace brown\rpc\application;
use brown\App;
use brown\App as SwooleApp;
use brown\concerns\WithContainer;

class Initialize extends App
{
    use WithContainer;
    protected $app;



    protected function prepareApplication($envName='')
    {

        if (!$this->app instanceof SwooleApp) {
            $this->app = new SwooleApp(parent::getRootPath());

            $this->app->bind(SwooleApp::class, \think\App::class);
            $this->app->setEnvName($envName);

            $this->app->initialize();
            $this->prepareConcretes();


        }
    }
    protected function prepareConcretes()
    {
        $defaultConcretes = ['db', 'cache', 'event'];
        $concretes = array_merge($defaultConcretes,$this->getSwooleConfig('concretes'));
        foreach ($concretes as $concrete) {
            if ($this->app->has($concrete)) {
                $this->app->make($concrete);
            }
        }
    }
    public function getSwooleConfig($name, $default = null){
        return $this->app->config->get("swoole.{$name}",$default);
    }
}