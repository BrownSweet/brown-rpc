## 简介

brown-rpc 是一款基于swoole的rpc拓展，可以通过composer安装，可在多个主流php框架中使用（laravel,thinkphp）。提供了链路追踪，服务注册发现，自定义命令行等功能。





##  功能

- ### 支持swoole协程

- ### 支持按类访问

- ### 支持服务注册与发现，默认提供consul为注册中心

- ### 支持链路追踪

- ### 支持命令行启动

- #### 服务既可以继续支持http访问，也可以当做rpc服务端使用



## 特点



- ### 遵循`composer psr-4`规则

- ### 适配多个框架

- ### 配置简单

- ### 客户端调用简单

- ### 内置注册中心

- ### 内置链路追踪



## 安装

composer拓展地址：https://packagist.org/packages/brown-rpc/brown-rpc

```
composer require brown-rpc/brown-rpc 选择版本
```





## 目录结构

在`vendor`目录下生成如下目录

```
/
├── brown-rpc    					项目目录
│	├── brown-rpc
│	│	├──src
│	│	│	├──client               
│	│	│	├──command
│	│	│	├──config
│	│	│	├──exceptions
│	│	│	├──middlewares
│	│	│	├──pack
│	│	│	├──register
│	│	│	├──request
│	│	│	├──response
│	│	│	├──sendfile
│	│	│	├──server
│	│	│	├──tracer
│	│	│	├──helper.php
│	│	│	├──LogicService.php
│	│	│	├──Manger.php
│	│	│	├──RpcClient.php
└──	└──	└──	└──RpcClient.php
```



## 使用



> ### 1、启动

·1、进入到拓展目录中：

```shell
#安装成功后
#进入到brown-rpc拓展目录中 注意目录层级！在有`composer.json`的目录中

cd vendor/brown-rpc/brown-rpc/

```

2、执行命令compsoer脚本

```shell
composer run-script post-package-install
```



3、执行命令以后会在项目根目录中生成 `brown`文件，执行 `php brown` 命令：

```
php brown
```

会看到如下命令：

```shell
Console Tool

Usage:
  command [options] [arguments]

Options:
  -h, --help            Display help for the given command. When no command is given display help for the list command
  -q, --quiet           Do not output any message
  -V, --version         Display this application version
      --ansi|--no-ansi  Force (or disable --no-ansi) ANSI output
  -n, --no-interaction  Do not ask any interactive question
  -v|vv|vvv, --verbose  Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug

Available commands:
  completion  Dump the shell completion script
  help        Display help for a command
  list        List commands
 rpc
  rpc:run     启动swoole服务  #启动命令

```



4、执行` php brown rpc:run` 启动服务

```
php brown rpc:run
```

会看到以下文字

```shell
/www/tp6-rpc/tp6-rpc-test/blog # php brown rpc:run
服务开启[2021-12-28T15:51:15.009155+08:00] brown.INFO: 启动 [] []
[2021-12-28T15:51:15.034100+08:00] brown.INFO: 服务已经注册中心 [] []

```



> ### 2.配置文件



在启动之前，按需配置好配置文件。



配置文件为`根目录/config/brown.php`

配置文件如下：

```php
<?php

return [
    'rpc'        => [
        'server' => [
            'enable'     => true,                            #默认开启rpc服务
            'host'       => '0.0.0.0',                       #服务监听地址
            'port'       => 9009,                            #服务监听端口
            'worker_num' => swoole_cpu_num(),                #work进程数
            'service_name'=>'default',                       #服务名称，按需填写
            'register'=>[
                'enable'=>true,                              #开启注册中心，默认不开启
                'class'=>\brown\register\Consul::class,      #注册中心API
                'uri'=>'http://120.53.236.218:8500',         #注册中心url
                'weight'=>1,                                 #设置当前服务的权重
                'host'=>'120.53.236.218'                     #注册中心开启后需要填写ip地址而非服务监听地址
            ], 
            'services'   => [                                #填写暴露的服务地址
                \App\Http\Controllers\Index::class
            ],

        ],
        'client' => [
            'register'=>[
                'enable'=>false,                            #客户端开启注册中心，默认不开启
                'uri'=>'http://127.0.0.1:8500',             #客户端注册中心url
                'class'=>\brown\register\Consul::class,     #注册中心API
                'service_name'=>[
                    'default'                               #服务名称 
                ]
            ],
            'default'=>[									#服务名称为键名
                'host'=>'127.0.0.1',                        #如果不开启注册中心需，则需要填写服务端ip
                'port'=>'9009'
            ],

        ],
    ],
    'hot_update' => [
        'enable'  => true,                                  #热更新，默认不开启
        'name'    => ['*.php'],                             #需要检测的热更新文件
        'include' => [dirname(__DIR__) . DIRECTORY_SEPARATOR], #检测的路径，建议填写app目录
        'exclude' => [],                                    #排除的路径 
    ],
    'parser'=>[
        'class'=>\brown\pack\Packer::class,                 #解析类
    ],
];

```



> ### 3、快速使用

首先准备客户端和服务端两个项目

客户端和服务端框架可以不一样，

例如：服务端采用laravel，客户端采用tp6,



在`laravel`根目录中，执行·`composer require brown-rpc/brown-rpc` 安装以后需要进入至目录 `vendor/brown-rpc/brown-rpc/`,执行`composer run-script post-package-install`,返回至项目根目录中，执行`php brown rpc:run`.

第一次执行应该会出现异常，需要到`根目录/config.php`，将服务暴露，按照配置文件中的使用介绍来配置相关参数。



laravel配置文件如下：

```php
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
                'enable'=>true,
                'class'=>\brown\register\Consul::class,
                'uri'=>'http://120.53.236.218:8500',
                'weight'=>1,
                'host'=>'120.53.236.218'
            ],
            'services'   => [
                \App\Http\Controllers\Index::class
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

```



Index.php如下：

```php
<?php

namespace App\Http\Controllers;

class Index extends Controller
{
    public function hello($name=11){
        return 'name'.$name;
    }
}
```

启动` php brown rpc:run`准备待用



客户端使用tp6

配置文件如下：

```php
<?php

use brown\websocket\socketio\Handler;

return [
    'rpc'        => [
        'server' => [
            'enable'     => true,
            'host'       => '120.53.236.218',
            'port'       => 9009,
            'worker_num' => swoole_cpu_num(),
            'service_name'=>'default',
            'register'=>[
                'enable'=>true,
                'class'=>\brown\register\Consul::class,
                'uri'=>'http://120.53.236.218:8500',
                'weight'=>1,
            ],
            'services'   => [

            ],

        ],
       'client' => [
            'register'=>[
                'enable'=>true,
                'uri'=>'http://120.53.236.218:8500',
                'class'=>\brown\register\Consul::class,
                'service_name'=>[
                    'default'
                ]
            ],
            'default'=>[
                'host'=>'120.53.236.218',
                'port'=>'9009'
            ],

        ],
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


];

```



在Index.php中使用如下：

```php
<?php
namespace app\controller;


use brown\RpcClient;

class Index
{
    public function index()
    {	
        //需要实例化RpcClient,Service中传入服务名称，request中传入需要调用服务端的class,Sync为参数true为同步方式、false为异步方式，hello为服务端Index类中的方法，参数为hello需要传递的参数
        echo (new RpcClient())->Service('default')->request('Index')->Sync(true)->hello([
            'name'=>111,
        ]);
    }

    public function hello($name = 'ThinkPHP6',$name1)
    {
        return 'hello,' . json_encode($name).json_encode($name1);
    }
}

```



在浏览器中访问的结果：

![image-20211228173541664](C:\Users\Administrator\AppData\Roaming\Typora\typora-user-images\image-20211228173541664.png)

> ### 4、注册中心

注册中心默认使用`consul `  ,如果需要使用其他注册中心需要实现 `brown\register\RegisterInterface`接口,并将配置文件中的register.class替换为其他类名称。



默认不开启注册中心，如果开启注册中心会在启动服务的时候，将服务注册到consul注册中心.

可以访问http://127.0.0.1:8500/ui/dc1/services查看注册的服务



![image-20211228170257557](C:\Users\Administrator\AppData\Roaming\Typora\typora-user-images\image-20211228170257557.png)

![image-20211228165832581](C:\Users\Administrator\AppData\Roaming\Typora\typora-user-images\image-20211228165832581.png)



不开启注册中心，将会采用单机模式。

如果服务关闭，则自动会将服务注销。

使用`deRegister`可以将服务注销



> ### 5、链路追踪



当服务越来越多的时候，有时候我们的服务端也可能变成客户端。每个服务可能需要很多个服务协同才能完成这个接口功能，如果链路上任何一个服务出现问题或者网络超时，都会形成导致接口调用失败。随着业务的不断扩张，服务之间互相调用会越来越复杂。那么就可能会登录各自的服务器去查看日志。链路追踪提供了完整的调用链过程，并提供了可视化的页面。



brown-rpc中默认使用`Zipkin`链路追踪，并可以查看调用的名称和层级。



默认访问http://127.0.0.1:9411/

![image-20211228171149067](C:\Users\Administrator\AppData\Roaming\Typora\typora-user-images\image-20211228171149067.png)







![image-20211228171159252](C:\Users\Administrator\AppData\Roaming\Typora\typora-user-images\image-20211228171159252.png)



## 软件安装



> ### Consul的安装



docker 安装 consul

```shell
$ docker pull consul 

$ docker run -d -p 8500:8500 --restart=always --name=consul consul:latest agent -server -bootstrap -ui -node=1 -client='0.0.0.0'

```



- agent: 表示启动 Agent 进程。
- server：表示启动 Consul Server 模式
- client：表示启动 Consul Cilent 模式。
- bootstrap：表示这个节点是 Server-Leader ，每个数据中心只能运行一台服务器。技术角度上讲 Leader 是通过 Raft 算法选举的，但是集群第一次启动时需要一个引导 Leader，在引导群集后，建议不要使用此标志。
- ui：表示启动 Web UI 管理器，默认开放端口 8500，所以上面使用 Docker 命令把 8500 端口对外开放。
- node：节点的名称，集群中必须是唯一的，默认是该节点的主机名。
- client：consul服务侦听地址，这个地址提供HTTP、DNS、RPC等服务，默认是127.0.0.1所以不对外提供服务，如果你要对外提供服务改成0.0.0.0
- join：表示加入到某一个集群中去。 如：-json=192.168.0.11。



> ### Zipkin的安装



docker 安装Zipkin

```shell
$ docker pull openzipkin/zipkin
$ docker run --name zipkin -d -p 9411:9411 openzipkin/zipkin
```



