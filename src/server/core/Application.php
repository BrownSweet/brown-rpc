<?php

namespace brown\server\core;

use Psr\Log\LoggerInterface;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

trait Application
{


    protected $now_path;
    protected $root_path;
    protected $config_path;
    protected $config=[];
    public $logger;
   public function __construct($root_path='')
   {

       $this->now_path=dirname(__DIR__) . DIRECTORY_SEPARATOR;

       $this->root_path    = $root_path?$root_path:dirname($this->now_path, 5) . DIRECTORY_SEPARATOR;

       $this->config_path=$this->root_path.'config'.DIRECTORY_SEPARATOR.'brown.php';

       $this->config=(array) include $this->config_path;

   }

   public function getRootPath(){
       return $this->root_path;
   }

//    /**
//     * @author Brown 2021/12/23 17:33
//     */
//    public function config(){
//        $this->config=(array) include $this->config_path;
//       return $this;
//    }


    /**
     * @param string $name
     * @return array
     * @author Brown 2021/12/23 18:07
     */
    protected function pull(string $name): array
    {
        $name = strtolower($name);

        return $this->config[$name] ?? [];
    }

    /**
     * @param string|null $name
     * @param null $default
     * @return array|mixed|null
     * @author Brown 2021/12/23 18:08
     */
    protected function getConfig(string $name = null, $default = null)
    {

        // 无参数时获取所有
        if (empty($name)) {
            return $this->config;
        }

        if (false === strpos($name, '.')) {
            return $this->pull($name);
        }

        $name    = explode('.', $name);
        $name[0] = strtolower($name[0]);
        $config  = $this->config;

        // 按.拆分成多维数组进行判断
        foreach ($name as $val) {
            if (isset($config[$val])) {
                $config = $config[$val];
            } else {
                return $default;
            }
        }

        return $config;
    }

    /**
     * 设置默认日志处理器
     *
     * @param LoggerInterface|null $logger
     * @author wuzhc 2021311 10:34:19
     */
    public function setDefaultLogger(LoggerInterface $logger = null)
    {
        if (empty($logger)) {
            $logger = new Logger('brown');
            $logger->pushHandler(new StreamHandler(STDOUT, Logger::DEBUG));
        }
        $this->logger = $logger;
    }
}