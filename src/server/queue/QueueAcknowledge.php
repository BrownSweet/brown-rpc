<?php
/**
 *   Author:Brown
 *   Email: 455764041@qq.com
 *   Time:
 */

namespace brown\server\queue;

interface QueueAcknowledge
{
    public function AcknowledgeMessage($queueName,$recv_msg ,$params=[]);
}