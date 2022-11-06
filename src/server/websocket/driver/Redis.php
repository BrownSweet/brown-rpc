<?php
/**
 *   Author:Brown
 *   Email: 455764041@qq.com
 *   Time: 2022/10/23 21:19
 */

namespace brown\server\websocket\driver;

use Swoole\Database\RedisConfig;
use Swoole\Database\RedisPool;

class Redis
{
    protected function initData(){
        $pool=new RedisPool(
            (new RedisConfig)
            ->withHost()
            ->withPort()
            ->withAuth()
            ->withDbIndex()
            ->withTimeout(1)
        );

    }
}