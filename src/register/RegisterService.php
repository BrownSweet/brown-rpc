<?php

/**
 *   Author:Brown
 *   Email: 455764041@qq.com
 *   Time: 2021/12/26 19:11
 */
namespace brown\register;
class RegisterService
{
    protected $host;
    protected $port;
    protected $weight;

    public function __construct($host, $port, $weight)
    {
        $this->host = $host;
        $this->port = $port;
        $this->weight = $weight;
    }

    public static function build($host, $port, $weight)
    {
        return new static($host, $port, $weight);
    }

    public function getHost()
    {
        return $this->host;
    }

    public function getPort()
    {
        return $this->port;
    }

    public function getWeight()
    {
        return $this->weight;
    }

    public function toArray(): array
    {
        return [
            'host'   => $this->host,
            'port'   => $this->port,
            'weight' => $this->weight
        ];
    }
}