<?php

namespace brown\request;



class AsyncRequest extends Request
{
    public function init()
    {
        $this->setSync(false);
        $this->setSystem(false);
    }
}