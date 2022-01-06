<?php

/**
 *   Author:Brown
 *   Email: 455764041@qq.com
 *   Time: 2022/01/06 21:07
 */
namespace brown\server\framework;
trait Initialize
{
    use Tp;

    public function frameworkInitialize()
    {
        if ($this->getConfig('framework.name')=='tp'){
            echo 2;
            if ($this->getConfig('framework.env')){
                $this->setEnv($this->getConfig('framework.env'))->init();
            }
        }
    }

}