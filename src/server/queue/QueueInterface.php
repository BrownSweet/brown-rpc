<?php
namespace brown\server\queue;
interface QueueInterface {



    public function connection(array $options=[]);

    public function createQueue($QueueName,array $option=[]);

    public function SendMessages($queueName,array $data,$DelaySeconds,$notifyUrl,$AutoAck='auto');

    public function SendBatchMessages();

    public function reciveMessage($queueName,array $option=[]);

    public function AcknowledgeMessage($queueName,$recv_msg ,array $option = []);
    
    public function createTopic($TopicName,$FilterType,$MsgRetentionSeconds);


    public function createSubscribe($TopicName,$SubscriptionName,$Protocol,$Type,array $TypeC,$url='');

    public function publishMessage($queue_name,$msg,array $tag,$route);

}