<?php

namespace brown\resetters;

use think\Container;
use brown\contract\ResetterInterface;
use brown\Sandbox;

class ResetConfig implements ResetterInterface
{

    public function handle(Container $app, Sandbox $sandbox)
    {
        $app->instance('config', clone $sandbox->getConfig());

        return $app;
    }
}
