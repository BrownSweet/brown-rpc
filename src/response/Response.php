<?php

namespace brown\response;



class Response
{
    const RES_ERROR = 500;
    const RES_SUCCESS = 200;

    public string $msg;
    public int $code;
    public array $data;

    public function __construct($code, $msg, $data)
    {
        $this->data = $data;
        $this->code = $code;
        $this->msg = $msg;
    }

    public static function error($msg, $code = self::RES_ERROR, $data = []): Response
    {
        return new static($code, $msg, $data);
    }

    public static function success($data = [], $msg = 'success', $code = self::RES_SUCCESS): Response
    {
        return new static($code, $msg, $data);
    }
}