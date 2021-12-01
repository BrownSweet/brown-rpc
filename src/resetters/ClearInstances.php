<?php

namespace brown\resetters;

use think\Container;
use brown\contract\ResetterInterface;
use brown\Sandbox;

class ClearInstances implements ResetterInterface
{
    public function handle(Container $app, Sandbox $sandbox)
    {
        $instances = ['log'];

        $instances = array_merge($instances, $sandbox->getConfig()->get('swoole.instances', []));

        foreach ($instances as $instance) {
            $app->delete($instance);
        }

        return $app;
    }
}
