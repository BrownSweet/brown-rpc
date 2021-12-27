<?php
/**
 *   Author:Brown
 *   Email: 455764041@qq.com
 *   Time: 2021/12/20 19:01
 */
namespace brown\request;


class SyncRequest extends Request
{
    public function init()
    {
        $this->setSync(true);
        $this->setSystem(false);
    }
}