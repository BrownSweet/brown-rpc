<?php
namespace brown\rpc\application;
use brown\App;
use brown\App as SwooleApp;
use brown\concerns\WithContainer;

class Initialize extends App
{
    use WithContainer;
    protected $app;



    protected function prepareApplication()
    {

        if (!$this->app instanceof SwooleApp) {
            $this->app = new SwooleApp(parent::getRootPath());
            $this->app->bind(SwooleApp::class, \think\App::class);
            $this->app->initialize();
        }
    }
}