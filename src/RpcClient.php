<?php
namespace brown;




use brown\client\Connector;
use brown\request\AsyncRequest;
use brown\request\Request;
use brown\request\SyncRequest;
use brown\server\core\Application;

class RpcClient extends LogicService
{

    use Connector;
    use Application;

    protected $services;
    protected $request;
    protected $method;

    protected $sync=true;
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
    public function sendRequest($arguments){
        if ($this->sync){
            $request=SyncRequest::create($this->services,$this->request,$this->method,$arguments[0],$this->getTracerContext(
                $this->getParentInfo()
            ));
            return $this->send($request);
        }else{
//            AsyncRequest::create();
        }
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
