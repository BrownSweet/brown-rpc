<?php

namespace brown\rpc\client;

use Generator;

interface Connector
{
    /**
     * @param Generator|string $data
     * @param callable $decoder
     * @return string
     */
    public function sendAndRecv($data, callable $decoder);
}
