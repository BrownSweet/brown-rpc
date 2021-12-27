<?php
namespace brown\server;
use brown\middlewares\TraceMiddleware;
use Swoole\Constant;
use Swoole\Process;
use Swoole\Process\Pool;


trait Server
{
    /** @var Pool */
    protected Pool $pool;

    protected $workId;
    public $options=[];

    public $middlewares=[
        TraceMiddleware::class
    ];

    protected array $defaultOptions
        = [
            'open_length_check'     => true,
            'package_length_type'   => 'N',
            'package_length_offset' => 0, //第N个字节是包长度的值
            'package_body_offset'   => 4, //第几个字节开始计算长度
            'package_max_length'    => 81920, //协议最大长度
        ];
    public function start(){

        $this->initialize();
        $this->setDefaultLogger();
        $this->HotUpdate();
        $this->logger->info('启动');
        $this->bindRegister();
        $pool = new Pool(count($this->startFuncArr), SWOOLE_IPC_UNIXSOCK, null, true);
        $pool->on(Constant::EVENT_WORKER_START, function ($pool, $workerId) {
            $this->pool=$pool;
            $this->workId=$workerId;
            Process::signal(SIGTERM, function () {
                $this->pool->getProcess()->exit();
            });
            $this->clearCache();
            $this->startFuncArr[$workerId]($pool, $workerId);
        });

        $pool->on(Constant::EVENT_WORKER_STOP,function ($pool, $workerId)
        {
            echo("[Worker #{$workerId}] WorkerStop\n");
        });
        $pool->start();
    }

    /**
     * 清除apc、op缓存
     */
    protected function clearCache()
    {
        if (extension_loaded('apc')) {
            apc_clear_cache();
        }

        if (extension_loaded('Zend OPcache')) {
            opcache_reset();
        }
    }

    /**
     * 设置默认选项
     *
     * @param $options
     * @author wuzhc 2021311 10:35:3
     */
    protected function setDefaultOptions($options)
    {
        if (empty($options)) {
            $options = $this->defaultOptions;
        }

        $this->options = $options;

//        //请求数量超过10000重启
//        if (empty($this->options['max_request'])) {
//            $this->options['max_request'] = 10000;
//        }
//        //默认task数量
//        if (empty($this->options['task_worker_num'])) {
//            $this->options['task_worker_num'] = swoole_cpu_num() * 2;
//        }
//        //task请求数超过10000则重启
//        if (empty($this->options['task_max_request'])) {
//            $this->options['task_max_request'] = 10000;
//        }
//        //10s没有数据传输就进行检测
//        if (empty($this->options['tcp_keepidle'])) {
//            $this->options['tcp_keepidle'] = 10;
//        }
//        //3s探测一次
//        if (empty($this->options['tcp_keepinterval'])) {
//            $this->options['tcp_keepinterval'] = 3;
//        }
//        //探测的次数，超过5次后还没回包close此连接
//        if (empty($this->options['tcp_keepcount'])) {
//            $this->options['tcp_keepcount'] = 5;
//        }
    }
}