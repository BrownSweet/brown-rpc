<?php

namespace brown;

use brown\command\RpcInterface;
use brown\command\Server as ServerCommand;

class Service extends \think\Service
{

    public function boot()
    {
        $this->commands(
            ServerCommand::class,
            RpcInterface::class
        );
    }

}
