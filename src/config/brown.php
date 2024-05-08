<?php

use Dtm\Constants\DbType;
use Dtm\Constants\Protocol;

return [
    'framework' =>[
        'name'=>'tp',
        'env'=>'testing'
    ],
    'rpc'        => [
        'protocol'=>'http',
        'server' => [
            'enable'     => true,
            'host'       => '0.0.0.0',
            'port'       => 9009,
            'worker_num' => swoole_cpu_num(),
            'service_name'=>['default'],
            'register'=>[
                'enable'=>false,
                'class'=>\brown\register\Consul::class,
                'uri'=>'http://127.0.0.1:8500',
                'weight'=>1,
                'host'=>'127.0.0.1',
                'port'=> 9009,
            ],
            'services'   => [

            ],

        ],
        'client' => [
            'timeout'=>60,
            'register'=>[
                'enable'=>false,
                'uri'=>'http://127.0.0.1:8500',
                'class'=>\brown\register\Consul::class,
                'service_name'=>[
                    'default'
                ]
            ],
            'default'=>[
                'host'=>'127.0.0.1',
                'port'=>'9009'
            ],

        ],
    ],
    'queue'=>[
        'enable'=>false,
        'worker_num'=>swoole_cpu_num(),
        'listen'=>[
//            'first_example',
//            'second_example'
        ],
        'worker'=>[
            //---------共享队列--------
//            "first_example,second_example"=>[\brown\server\queue\FirstExample::getInstance()],
//            "first_example,second_example"=>[\brown\server\queue\FirstExample::getInstance(),\brown\server\queue\SecondExample::getInstance()],
           //---------独立队列--------
//            "second_example"=>\brown\server\queue\SecondExample::getInstance(),
//            "first_example"=>\brown\server\queue\FirstExample::getInstance(),
//            "second_example"=>\brown\server\queue\SecondExample::getInstance(),
        ],

        'handlers'=>[
//            'first_example,second_example'=>[
//                \app\controller\FirstHandler::class,
//                \app\controller\SecondHandler::class
//            ],
//            'first_example'=>[
//                \app\controller\OtherHandler::class,
//            ]
        ],
        'ack'=>[
//            'first_example'=>[
//                \app\controller\Ack::class,
//            ]
        ]
    ],
    'hot_update' => [
        'enable'  => false,
        'name'    => ['*.php'],
        'include' => [dirname(__DIR__) . DIRECTORY_SEPARATOR],
        'exclude' => [],
    ],
    'parser'=>[
        'class'=>\brown\pack\Packer::class,
    ],
    'trace'=>[
        'tracerUrl'=>'http://127.0.0.1:9411/api/v2/spans'
    ],
    'dtm'=>[
        'protocol' => Protocol::HTTP,
        'server' => '82.157.58.76',
        'port' => [
            'http' => 36789,
            'grpc' => 36790,
        ],
        'barrier' => [
            'db' => [
                'type' => DbType::MySQL,
            ],
            'apply' => [],
        ],
        'guzzle' => [
            'options' => [],
        ],
    ]
];
