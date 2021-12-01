<?php

namespace brown\contract;

use think\Container;
use brown\Sandbox;

interface ResetterInterface
{
    /**
     * "handle" function for resetting app.
     *
     * @param Container $app
     * @param Sandbox   $sandbox
     */
    public function handle(Container $app, Sandbox $sandbox);
}
