<?php
/**
 *   Author:Brown
 *   Email: 455764041@qq.com
 *   Time:
 */

namespace brown\server\queue;
use Predis\Client;
class Redis implements QueueInterface,DelayQueueInterface
{
    private static $instance;
    private $redisClient;

    private function __construct()
    {
        $this->connect();
    }

    private function connect(): void
    {
        $this->redisClient = new Client([
            "scheme" => "tcp",
            "host" => "127.0.0.1",
            "port" => "6379",
            "password" => "123456",
            "timeout" => -1,
            'persistent' => true,
            'read_write_timeout' => 0,
        ], ['database' => 0]);
    }

    public static function getInstance(): self
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    public function delayReciveMessage($queueName,$params = [])
    {
        // TODO: Implement delayReciveMessage() method.
        [$queue, $payload]= $this->redisClient->zRangeByScore($queueName,0,time(),1);
        return $payload ?? null;

    }

    public function reciveMessage($queueName, $params = [])
    {
        [$queue, $payload] = $this->redisClient->blPop($queueName, 0);
        return $payload ?? null;
        // TODO: Implement reciveMessage() method.
    }

    public function sendMessage($queueName, $message, array $params = [])
    {
        return $this->redisClient->rPush($queueName, $message);
    }

    public function sendDelayMessage($queueName,$message,$delaySeconds,$params = [])
    {
        return $this->redisClient->zAdd($queueName,time()+$delaySeconds,$message);

    }


}