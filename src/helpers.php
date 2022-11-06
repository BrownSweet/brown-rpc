<?php

if (!function_exists('swoole_cpu_num')) {
    function swoole_cpu_num(): int
    {
        return 1;
    }
}

if (!defined('SWOOLE_SOCK_TCP')) {
    define('SWOOLE_SOCK_TCP', 1);
}

if (!defined('SWOOLE_PROCESS')) {
    define('SWOOLE_PROCESS', 3);
}

if (!defined('SWOOLE_HOOK_ALL')) {
    define('SWOOLE_HOOK_ALL', 1879048191);
}
if (!function_exists('SendRequest')){
    function SendRequest($reqUrl,$sendData,array $header=[]){
        $httpClient = new \GuzzleHttp\Client();
        $request = $httpClient->post($reqUrl, [
            'body'    => $sendData,
            'headers' => $header
        ]);
        return $request->getStatusCode();
    }
}

if(!function_exists('time_after')){
    function time_after(int $ms_timeout,callable $callback){
        return \Swoole\Timer::after($ms_timeout,$callback);
    }
}

if (!function_exists('time_clear')){
    function time_clear(int $time_id){
        return \Swoole\Timer::clear($time_id);
    }
}

