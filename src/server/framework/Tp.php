<?php

/**
 *   Author:Brown
 *   Email: 455764041@qq.com
 *   Time: 2022/01/06 21:08
 */
namespace brown\server\framework;
use think\App;

trait Tp
{
    /**
     * @pr
     * @author Brown 2022/1/6 21:11
     */
    protected $app;
    /**
     * @var App
     * @author Brown 2022/1/6 21:11
     */
    public function app(){
        $this->app=new App();
        return $this;
    }

    public function setEnv($env=''){
        if (!($this->app instanceof App)){
            $this->app();
        }
        if ($env){
            $this->app->setEnvName($env)->initialize();
        }else{
            $this->app->initialize();
        }

    }

    public function init(){
        $this->app->initialize();
    }
}