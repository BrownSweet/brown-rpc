<?php
namespace brown\server\queue;
interface DelayQueueInterface {


    public function delayReciveMessage($queueName,$params=[]);

}