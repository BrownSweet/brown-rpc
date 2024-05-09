<?php
/**
 *   Author:Brown
 *   Email: 455764041@qq.com
 *   Time: 2021/12/23 21:35
 */

namespace brown\server\core;


use brown\exceptions\RpcException;
use brown\LogicService;
use ReflectionClass;

trait Services
{
    protected $serialization;
    public $services=[];

    /**绑定服务注册中心
     * @author Brown 2021/12/23 20:19
     */
    public function bindRpcService(){
        foreach ($this->getConfig('rpc.server.services') as $key=>$services){
            foreach ($services as $className){
                try {
                    $reflectionClass = new ReflectionClass($className);
                    $interfaces      = $reflectionClass->getInterfaceNames();
                }catch (RpcException $e){
                    throw new RpcException('class is not an object.', ['service' => $className]);
                }

                if (!empty($interfaces)) {
                    foreach ($interfaces as $interface) {
                        $this->services[$key][class_basename($interface)] = [
                            'interface' => $interface,
                            'class'     => $className,

                        ];
                    }
                } else {
                    $this->services[$key][class_basename($className)] = [
                        'interface' => $className,
                        'class'     => $className,
                    ];
                }
            }

        }

    }

    /**绑定序列化和反序列化
     * @author Brown 2021/12/23 20:19
     */
    public function bindRpcSerialization(){
        $this->serialization=$this->getConfig('parser.class');

    }

    protected function getService(){
        $this->services=$this->getConfig('rpc.server.services');
        return $this->services;
    }

    protected function addInstance($obj, $prefix = ''){

        if (is_string($obj)) {
            $obj = new $obj();
        }

        if (!is_object($obj)) {
            $this->logger->error('Service is not an object.', ['service' => $obj]);
            return false;
        }
//        if (!($obj instanceof LogicService)) {
//            $this->logger->error('The Service does not inherit LogicService', ['service' => get_class($obj)]);
//            return false;
//        }

        $className = get_class($obj);
        $methods = get_class_methods($obj);
        foreach ($methods as $method) {
            if (in_array($method, get_class_methods(LogicService::class))) {
                continue;
            }
            if (strlen($prefix) > 0) {
                $key = $prefix . '_' . $className . '_' . $method;
            } else {
                $key = $className . '_' . $method;
            }
            $this->services[$key] = $className;
            $this->logger->info(sprintf('import %s => %s.', $key, $className));
        }
        return true;
    }

    public function bindRegister(){

        if($this->getConfig('rpc.server.register.enable')){
            $Register=$this->getConfig('rpc.server.register.class');
            $r_uri=$this->getConfig('rpc.server.register.uri');
            $r_c=new $Register($r_uri);
            $host = $this->getConfig('rpc.server.register.host', '0.0.0.0');
            if ($host=='0.0.0.0'){
                $this->logger->info('检测到开启注册中心，请将host填写为ip地址而非服务监听地址');
                throw new RpcException('检测到开启注册中心，请将host填写为ip地址而非服务监听地址');
            }
            $service = $this->getConfig('rpc.server.service_name', 'default');
            $port = $this->getConfig('rpc.server.register.port', 9009);
            $weight=$this->getConfig('rpc.server.register.weight', '10');
            foreach ($service as $key=>$service_name){
                $r_c->register($service_name,$host,$port,$key,$weight);
            }
            $this->logger->info('服务已注册,登录注册中心查看'.$r_uri);
        }
    }

    public function deregister(){
        if($this->getConfig('rpc.server.register.enable')){
            $Register=$this->getConfig('rpc.server.register.class');
            $r_c=new $Register($this->getConfig('rpc.server.register.uri'));
            $host = $this->getConfig('rpc.server.register.host', '0.0.0.0');
            $port = $this->getConfig('rpc.server.port', 9009);
            $r_c->unRegister($host,$port);
            $this->logger->info('服务已注销');
        }
    }
}