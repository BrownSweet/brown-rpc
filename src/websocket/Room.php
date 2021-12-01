<?php

namespace brown\websocket;

use think\Manager;
use brown\websocket\room\Table;

/**
 * Class Room
 * @package brown\websocket
 * @mixin Table
 */
class Room extends Manager
{
    protected $namespace = "\\think\\swoole\\websocket\\room\\";

    protected function resolveConfig(string $name)
    {
        return $this->app->config->get("swoole.websocket.room.{$name}", []);
    }

    /**
     * 默认驱动
     * @return string|null
     */
    public function getDefaultDriver()
    {
        return $this->app->config->get('swoole.websocket.room.type', 'table');
    }
}
