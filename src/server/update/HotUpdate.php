<?php

/**
 *   Author:Brown
 *   Email: 455764041@qq.com
 *   Time: 2021/12/23 18:16
 */
namespace brown\server\update;

use brown\server\update\FileWatch;
use Swoole\Process;

trait HotUpdate
{
    protected function HotUpdate(){
        if ($this->getConfig('hot_update.enable', false)) {

            $this->addOneWorker(function (Process\Pool $pool){
                $this->setProcessName('hot update');

                $watcher = new FileWatch(
                    $this->getConfig('hot_update.include', []),
                    $this->getConfig('hot_update.exclude', []),
                    $this->getConfig('hot_update.name', [])
                );

                $watcher->watch(function () use ($pool) {
                    Process::kill($pool->master_pid, SIGUSR1);
                });
            });

        }
    }
}