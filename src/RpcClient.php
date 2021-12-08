<?php
namespace brown;
use Smf\ConnectionPool\ConnectionPool;
use Swoole\Client;
use think\App;
use think\File;
use brown\concerns\InteractsWithRpcConnector;

use brown\concerns\WithApplication;
use brown\concerns\WithContainer;
use brown\exception\RpcClientException;
use brown\rpc\client\Connector;
use brown\rpc\client\Gateway;
use brown\rpc\client\Proxy;
use brown\rpc\Error;
use brown\rpc\JsonParser;
use brown\rpc\Packer;
use brown\rpc\Protocol;
use brown\rpc\Sendfile;


class RpcClient
{
    use Sendfile;
    use WithContainer;
    use WithApplication;

    protected $interface;

    /** @var Gateway */
    protected $gateway;

    /** @var App */
    protected $app;

    protected $middleware = [];

    protected $parser;

    protected $client;
    protected $this_service;
    protected $this_request_body;
    protected $this_request_name;
    protected $this_method;
    protected $send_data;
    protected $request_prefix='request@';
    protected $method_prefix='method@';
    protected $parameter_prefix='parameter@';
    protected $parameter;
    protected $config;
    public function __construct()
    {
        $this->app = new App();

        $this->parser = new JsonParser();
    }

    public function __call($name, $arguments)
    {
        if ($name == 'Service') {
            return $this->checkService($arguments);
        } elseif ($name == 'request') {
            return $this->checkQuest($arguments);
        }else{
            return $this->checkMethod($name,$arguments)->sendAndRecv();
        }


    }

    protected function checkService($service)
    {
        if (file_exists($swoole_config = $this->app->getConfigPath() . 'swoole.php')) {
            $rpc_swoole_service = (array)include $swoole_config;
            if (isset($rpc_swoole_service['rpc']['client'][$service[0]])) {
                $this->config = $rpc_swoole_service['rpc']['client'][$service[0]];
                $this->this_service = $service[0];
                return $this;
            }
        }
    }

    protected function checkQuest($quest)
    {
//        print_r($quest);
        if (file_exists($rpc = $this->app->getBasePath() . 'rpc.php')) {
            $rpcServices = (array) include $rpc;
            if (isset($rpcServices[$this->this_service][$this->request_prefix.$quest[0]])){
                $this->this_request_body=$rpcServices[$this->this_service][$this->request_prefix.$quest[0]];
            }
            $this->this_request_name=$quest[0];
            return $this;
        }
    }

    protected function checkMethod($method,$params){

        if (isset($this->this_request_body[$this->method_prefix.$method])){
            $this->this_method=$method;
            $this->parameter=$params[0];
            return $this;
        }

    }

    protected function sendAndRecv(){

        $client = new Client(SWOOLE_SOCK_TCP);
        if (!$client->connect($this->config['host'], $this->config['port'], -1)) {
            throw new RpcClientException('Connection is closed. ' . $client->errMsg, $client->errCode);
        }

        $data=$this->encodeData($this->proxyCall($this->this_request_name,$this->this_method,$this->parameter));
        if (!$data instanceof \Generator) {
            $data = [$data];
        }
        if (!$data instanceof \Generator) {
            $data = [$data];
        }

        foreach ($data as $string) {
            if (!empty($string)) {
                if ($client->send($string) === false) {
                    throw new RpcClientException('Send data failed. ' . $client->errMsg, $client->errCode);
                }else{

                }
            }
        }

        $data=$client->recv();

        $client->close();

        return $this->decodeData($data);
    }

    protected function encodeData(Protocol $protocol)
    {
        $params = $protocol->getParams();

        //有文件,先传输
        foreach ($params as $index => $param) {
            if ($param instanceof File) {
                yield from $this->fread($param);
                $params[$index] = Protocol::FILE;
            }
        }

        $protocol->setParams($params);

        $data = $this->parser->encode($protocol);

        yield Packer::pack($data);
    }
    protected function proxyCall($service,$method, $params)
    {

        $protocol = Protocol::make($service, $method, $params);

        return $protocol;

    }

    protected function decodeData($data){
        [$handler, $data] = Packer::unpack($data);
        $result=$handler->write($data);
        $res=json_decode($result,true);
        return $res['result'];
    }

}
