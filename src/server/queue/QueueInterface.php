<?php
namespace brown\server\queue;
interface QueueInterface {


    public function reciveMessage($queueName,$params=[]);

}