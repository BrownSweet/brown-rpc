<?php

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
            'service_name'=>'default',
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
        'enable'=>true,
        'worker_num'=>swoole_cpu_num(),
        'worker'=>[
            "first_example"=>\brown\server\queue\FirstExample::getInstance(),
            "second_example"=>\brown\server\queue\SecondExample::getInstance(),
        ],
        'listen'=>[
            'first_example',
            'second_example'
        ],
        'handlers'=>[
            'first_example'=>[
                \app\controller\FirstHandler::class,
                \app\controller\SecondHandler::class
            ],
            'second_example'=>[
                \app\controller\SecondHandler::class,
                \app\controller\OtherHandler::class,

            ]
        ],
        'ack'=>[
            'first_example'=>[
                \app\controller\Ack::class,
            ]
        ]
    ],
    'hot_update' => [
        'enable'  => true,
        'name'    => ['*.php'],
        'include' => [dirname(__DIR__) . DIRECTORY_SEPARATOR],
        'exclude' => [],
    ],
    'parser'=>[
        'class'=>\brown\pack\Packer::class,
    ],
    'trace'=>[
        'tracerUrl'=>'http://127.0.0.1:9411/api/v2/spans'
    ]
];
