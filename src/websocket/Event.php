<?php

namespace brown\websocket;

class Event
{
    public $type;
    public $data;

    public function __construct($type, $data)
    {
        $this->type = $type;
        $this->data = $data;
    }
}
