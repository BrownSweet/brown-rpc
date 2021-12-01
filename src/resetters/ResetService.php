<?php

namespace brown\resetters;

use think\Container;
use brown\concerns\ModifyProperty;
use brown\contract\ResetterInterface;
use brown\Sandbox;

/**
 * Class ResetService
 * @package brown\resetters
 * @property Container $app;
 */
class ResetService implements ResetterInterface
{
    use ModifyProperty;

    /**
     * "handle" function for resetting app.
     *
     * @param Container $app
     * @param Sandbox   $sandbox
     */
    public function handle(Container $app, Sandbox $sandbox)
    {
        foreach ($sandbox->getServices() as $service) {
            $this->modifyProperty($service, $app);
            if (method_exists($service, 'register')) {
                $service->register();
            }
            if (method_exists($service, 'boot')) {
                $app->invoke([$service, 'boot']);
            }
        }
    }

}
