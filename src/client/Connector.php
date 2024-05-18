<?php

/**
 *   Author:Brown
 *   Email: 455764041@qq.com
 *   Time: 2021/12/24 21:54
 */
namespace brown\client;


use brown\request\Request;
use brown\response\Response;
use brown\sendfile\FileBase;
use GuzzleHttp\Exception\ConnectException;
use Swoole\Client;
use brown\exceptions\RpcException;
use const http\Client\Curl\HTTP_VERSION_2_0;

trait Connector
{
    protected $parser;
    public function connect($proto='tcp')
    {
        $registerEnabled = $this->getConfig('rpc.client.register.enable');

// 尝试从注册中心获取服务信息
        if ($registerEnabled) {
            $registerClass = $this->getConfig('rpc.client.register.class');
            $uri = $this->getConfig('rpc.client.register.uri');
            $r_w = new $registerClass($uri);
            $services = $r_w->getServices($this->services);

            // 检查注册中心是否有服务
            if (count($services) > 0) {
                $host = $services[0]->getHost();
                $port = $services[0]->getPort();
            } else {
                // 注册中心无服务，使用内置配置
                $config = $this->getConfig('rpc.client');
                if (!isset($config[$this->services])) {
                    throw new RpcException("服务不存在");
                }
                $host = $config[$this->services]['host'];
                $port = $config[$this->services]['port'];
            }
        } else {
            // 注册中心未启用，直接使用内置配置
            $config = $this->getConfig('rpc.client');
            if (!isset($config[$this->services])) {
                throw new RpcException("服务不存在");
            }
            $host = $config[$this->services]['host'];
            $port = $config[$this->services]['port'];
        }

        $timeout=$this->getConfig('rpc.client.timeout');

        $client = new \GuzzleHttp\Client(['base_uri'=>$host.':'.$port,'timeout'=>$timeout,
            'curl' => [ // cURL选项
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_2_PRIOR_KNOWLEDGE, // 告诉cURL优先使用HTTP/2，但注意这可能会导致与不支持HTTP/2的服务器通信失败
            ],]);
//        $client=new Client(SWOOLE_SOCK_TCP);
//        if (!$client->connect($host,$port,$timeout)){
//            throw new RpcException("连接失败");
//        }

        return $client;
    }

    public function send(Request $request){


        if (!$this->parser){
            $parser=$this->getConfig('parser.class');
            $this->parser=new $parser();
        }

        $data=$this->encodeData($request,$this->parser);
        $conn=$this->connect($request->getProtocol());
//        $request = new \GuzzleHttp\Psr7\Request('POST', '/', [], $data);
////
//        $response = $conn->send($request);
////
//        $result = unserialize($response->getBody()->getContents());
//
//        die;
        if ($request->getProtocol()=='http'){
            // 构建HTTP POST请求
            $post_data = $data;
            $http_request = "POST / HTTP/1.1\r\n";
            $http_request .= "Host: www.example.com\r\n";
            $http_request .= "Content-Type: application/x-www-form-urlencoded\r\n";
            $http_request .= "Content-Length: " . strlen($post_data) . "\r\n";
            $http_request .= "Connection: close\r\n";
            $http_request .= "User-Agent: brown-rpc-client\r\n";
            $http_request .= "\r\n";
            $http_request .= $post_data;
            $data=$http_request;
        }


        if ($conn instanceof \GuzzleHttp\Client){
            $request = new \GuzzleHttp\Psr7\Request('POST', '/', [], $data);

            $response = $conn->send($request);

            $result = unserialize($response->getBody()->getContents());
        }elseif ($conn instanceof Client){
            if (!$data instanceof \Generator){
                $data=[$data];
            }

            foreach ($data as $string) {

                if (!empty($string)) {

                    if ($conn->send($string) === false) {
                        throw new RpcException('Send data failed. ' .  $conn->errCode);
                    }
                }
            }
//            print_r($data);
//            die;
        if ($conn->send($data) === false) {
            throw new RpcException('Send data failed. ' .  $conn->errCode);
        }
        $res=$conn->recv(65536,Client::MSG_WAITALL);
        if ($request->getProtocol()=='http'){
            $parts = explode("\r\n\r\n", $res, 2);
            $res = $parts[1];
        }

        $result=unserialize($res);
//            $result=unserialize($conn->recv(65536,Client::MSG_WAITALL));
        }

//        try {
//            $conn=$this->connect('http');
//
//            $request = new \GuzzleHttp\Psr7\Request('POST', '/', [], $data);
//
//            $response = $conn->send($request);
//
//            $result = unserialize($response->getBody()->getContents());
//        }catch (ConnectException $e){
//            $conn=$this->connect();
//            if (!$data instanceof \Generator){
//                $data=[$data];
//            }
//
//            foreach ($data as $string) {
//
//                if (!empty($string)) {
//
//                    if ($conn->send($string) === false) {
//                        throw new RpcException('Send data failed. ' .  $conn->errCode);
//                    }
//                }
//            }
//            $result=unserialize($conn->recv(65536,Client::MSG_WAITALL));
//        }
        return $result;
//        if (!($result instanceof Response)){
//            throw new RpcException('错误的响应');
//        }
//
//        if ($result->code ==Response::RES_ERROR){
//            throw new RpcException($result->msg);
//        }
//        return $result->data['result'];
    }

    protected function encodeData(Request $request,$parser)
    {
        $params = $request->getParams();

        //有文件,先传输
        foreach ($params as $index => $param) {
            if (is_array($param)){
                foreach ($param as $key=>$value){
                    if ($value instanceof FileBase){
                        $handle = fopen($value->getPathname(), 'rb');
                        $file[$key]= fread($handle, 8192);
                        unset($params[$index]);
                    }
                }
            }else{
                if ($param instanceof FileBase) {
                    $handle = fopen($param->getPathname(), 'rb');
                    $file=[
                        $index=>  fread($handle, 8192)
                    ];
                    unset($params[$index]);

                }
            }
        }
        if (isset($file)){
            $request->setFile($file);
        }
        $request->setParams($params);

        return $parser->pack($request);
    }
    public function sizecount($filesize) {
        if($filesize >= 1073741824) {
            $filesize = round($filesize / 1073741824 * 100) / 100 . ' gb';

        } elseif($filesize >= 1048576) {
            $filesize = round($filesize / 1048576 * 100) / 100 . ' mb';

        } elseif($filesize >= 1024) {
            $filesize = round($filesize / 1024 * 100) / 100 . ' kb';

        } else {
            $filesize = $filesize . ' bytes';

        }

        return $filesize;

    }
}