<?php

namespace brown;

use brown\server\core\Application;
use brown\server\rpc\RpcServer;
use brown\server\Server;
use brown\server\update\HotUpdate;
use brown\server\core\WorkerManger;
use brown\server\core\Services;
/**
 * Class Manager
 */
class Manager
{
    use Services;
    use Server;
    use RpcServer;
    use HotUpdate;
    use WorkerManger;
    use Application;

    /**
     * Initialize.
     */
    protected function initialize():void
    {
        $this->loadRpcServer();
    }


}
