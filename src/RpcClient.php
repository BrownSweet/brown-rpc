<?php
namespace brown;




use brown\client\Connector;
use brown\exceptions\RpcException;
use brown\request\AsyncRequest;
use brown\request\Request;
use brown\request\SyncRequest;
use brown\response\Response;
use brown\server\core\Application;

class RpcClient extends LogicService
{

    use Connector;
    use Application;

    protected $services;
    protected $request;
    protected $method;
    protected $callback=null;

    protected $sync=true;
    protected $ptocol='';
    protected array $defaultOptions
        = [
            'open_length_check'     => true,
            'package_length_type'   => 'N',
            'package_length_offset' => 0, //第N个字节是包长度的值
            'package_body_offset'   => 4, //第几个字节开始计算长度
            'package_max_length'    => 81920, //协议最大长度
        ];
    protected array $options;
    public function __call($name, $arguments)
    {
//
        switch ($name){
            case 'Service':
                return $this->setService($arguments);
                break;
            case 'request':
                return $this->setRequest($arguments);
                break;
            default:
                return $this->setMethod($name)->sendRequest($arguments);
                break;
        }
    }
    public function setService($arguments){
        $this->setModule($arguments[0]);
        $this->services=$arguments[0];
        return $this;
    }
    public function setRequest($arguments){
        $this->request=$arguments[0];
        return $this;
    }
    public function setMethod($method){
        $this->method=$method;
        return $this;
    }
    public function Sync($sync=true){
        $this->sync=$sync;
        return $this;
    }
    public function callback($callback){
        $this->callback=$callback;
        return $this;
    }
    public function setProtocol($protocol){
        $this->ptocol=$protocol;
        return $this;
    }
    private $maxRetries = 3;
    private $retryInterval = 1000; // 单位：毫秒
    private $circuitBreakerStatus = 'CLOSED';
    private $failureThreshold = 3;
    private $failureWindow = 10; // 单位：秒
    private $lastFailureTime = null;
    public function sendRequest($arguments){

        $this->setTracerUrl($this->getConfig('trace.tracerUrl'));
        $this->setProtocol($this->getConfig('rpc.protocol'));
        if ($this->sync){
            $request=SyncRequest::create($this->services,$this->request,$this->method,$arguments[0],$this->ptocol,$this->getTracerContext(
                $this->getParentInfo()
            ));
            if ($this->isCircuitOpen()){
                return $this->fallbackAction($request->getService());
            }
            for ($i=0;$i<=$this->maxRetries;$i++){
                try {
                    $response=$this->send($request);
                    $this->resetCircuitBreaker();
                    if ($response->code ==Response::RES_ERROR){
                        throw new RpcException($response->msg);
                    }
                    return $response->data['result'];
                }catch (RpcException $e){
                    $this->handleFailure($e);
                    if ($i<$this->maxRetries){
                        usleep($this->retryInterval*1000);
                    }else{
                        $this->tripCircuitBreaker();
                        return $this->fallbackAction($request->getService());
                    }
                }
            }

        }else{
            $request=AsyncRequest::create($this->services,$this->request,$this->method,$arguments[0],$this->ptocol,$this->getTracerContext(
                $this->getParentInfo()
            ));
            if (is_callable($this->callback)){
                call_user_func($this->callback,$this->send($request));
            }
        }
    }
    private function isCircuitOpen() {
        if ($this->circuitBreakerStatus === 'OPEN' &&
            time() - $this->lastFailureTime < $this->failureWindow) {
            return true;
        }
        return false;
    }

    private function tripCircuitBreaker() {
        $this->circuitBreakerStatus = 'OPEN';
        $this->lastFailureTime = time();
    }

    private function resetCircuitBreaker() {
        $this->circuitBreakerStatus = 'CLOSED';
        $this->lastFailureTime = null;
    }

    private function handleFailure(\Exception $e) {
        if ($this->failureCounter() >= $this->failureThreshold) {
            $this->tripCircuitBreaker();
        }
    }
    private function failureCounter() {
        if ($this->lastFailureTime !== null) {
            $secondsSinceLastFailure = time() - $this->lastFailureTime;
            return min($secondsSinceLastFailure / $this->failureWindow, $this->failureThreshold);
        }
        return 0;
    }

    private function fallbackAction($service) {
        // 实现你的降级逻辑，如返回默认值、使用本地缓存、返回错误消息等
        return "服务降级：$service 不可用，返回默认值";
    }
    public function setOptions($options=[]){
        $this->options=array_merge($this->defaultOptions,$options);
        return $this;
    }

    protected function get_local_ip() {
        $preg = "/\A((([0-9]?[0-9])|(1[0-9]{2})|(2[0-4][0-9])|(25[0-5]))\.){3}(([0-9]?[0-9])|(1[0-9]{2})|(2[0-4][0-9])|(25[0-5]))\Z/";
//获取操作系统为win2000/xp、win7的本机IP真实地址
        exec("ipconfig", $out, $stats);
        if (!empty($out)) {
            foreach ($out AS $row) {
                if (strstr($row, "IP") && strstr($row, ":") && !strstr($row, "IPv6")) {
                    $tmpIp = explode(":", $row);
                    if (preg_match($preg, trim($tmpIp[1]))) {
                        return trim($tmpIp[1]);
                    }
                }
            }
        }
//获取操作系统为linux类型的本机IP真实地址
        exec("ifconfig", $out, $stats);
        if (!empty($out)) {
            if (isset($out[1]) && strstr($out[1], 'addr:')) {
                $tmpArray = explode(":", $out[1]);
                $tmpIp = explode(" ", $tmpArray[1]);
                if (preg_match($preg, trim($tmpIp[0]))) {
                    return trim($tmpIp[0]);
                }
            }
        }
        return '127.0.0.1';
    }

    protected function getParentInfo(){
        $array=debug_backtrace();

        $parent=$array[2];
        $file=$parent['file'];
        $line=$parent['line'];
        $service=$parent['object']->services;
        $request=$parent['object']->request;
        $method=$parent['object']->method;
        $args=$parent['args'][1][0];

        return 'ip:'.$this->get_local_ip().'文件:'.$file.'  第'.$line.'行'.',服务名称为'.$service.',请求的类'.$request.',方法'.$method.',参数'.json_encode($args);

    }

}
