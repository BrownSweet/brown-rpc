<?php
/**
 *   Author:Brown
 *   Email: 455764041@qq.com
 *   Time: 2021/12/23 18:19
 */

namespace brown\server\core;

use Swoole\Process\Pool;

trait WorkerManger
{
    /**
     * @var array
     */
    protected $startFuncArr = [];

    public function addMoreWorker(int $workerNum, callable $func,$name=null)
    {
        for ($i = 0; $i < $workerNum; $i++) {
            $this->addOneWorker($func,$name?"{$name} #{$i}":null);
        }
        return $this;
    }

    public function addOneWorker(callable $func,$name=null): self
    {

        $this->startFuncArr[] = [$func,$name];
        return $this;
    }

    /**
     * Set process name.
     *
     * @param $process
     */
    public function setProcessName($process)
    {
        $serverName = 'swoole';
        $appName    = 'brown';

        $name = sprintf('%s: %s for %s', $serverName, $process, $appName);

        @cli_set_process_title($name);
    }
}