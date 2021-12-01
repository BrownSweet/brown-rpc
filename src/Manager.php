<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: yunwuxin <448901948@qq.com>
// +----------------------------------------------------------------------

namespace brown;

use brown\concerns\InteractsWithHttp;
use brown\concerns\InteractsWithPools;
use brown\concerns\InteractsWithQueue;
use brown\concerns\InteractsWithRpcClient;
use brown\concerns\InteractsWithRpcServer;
use brown\concerns\InteractsWithServer;
use brown\concerns\InteractsWithSwooleTable;
use brown\concerns\WithApplication;
use brown\concerns\WithContainer;

/**
 * Class Manager
 */
class Manager
{
    use InteractsWithServer,
        InteractsWithSwooleTable,
        InteractsWithHttp,
        InteractsWithPools,
        InteractsWithRpcClient,
        InteractsWithRpcServer,
        InteractsWithQueue,
        WithContainer,
        WithApplication;

    /**
     * Initialize.
     */
    protected function initialize(): void
    {
        $this->prepareTables();
        $this->preparePools();
        $this->prepareHttp();
        $this->prepareRpcServer();
        $this->prepareQueue();
        $this->prepareRpcClient();
    }

}
