<?php

namespace brown\resetters;

use think\Container;
use brown\concerns\ModifyProperty;
use brown\contract\ResetterInterface;
use brown\Sandbox;

/**
 * Class ResetEvent
 * @package brown\resetters
 * @property Container $app;
 */
class ResetEvent implements ResetterInterface
{
    use ModifyProperty;

    public function handle(Container $app, Sandbox $sandbox)
    {
        $event = clone $sandbox->getEvent();
        $this->modifyProperty($event, $app);
        $app->instance('event', $event);

        return $app;
    }
}
