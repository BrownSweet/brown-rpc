<?php



return [
    'rpc'        => [
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
                'host'=>'127.0.0.1'
            ],
            'services'   => [

            ],

        ],
        'client' => [
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
    'hot_update' => [
        'enable'  => true,
        'name'    => ['*.php'],
        'include' => [dirname(__DIR__) . DIRECTORY_SEPARATOR],
        'exclude' => [],
    ],
    'parser'=>[
        'class'=>\brown\pack\Packer::class,
    ],
];
