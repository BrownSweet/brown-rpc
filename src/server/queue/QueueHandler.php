<?php
namespace brown\server\queue;
interface QueueHandler {

    public function handle($queueName,$recv_msg ,$params=[]);

}