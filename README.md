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

- 内置TCP/HTTP调用



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

### 1、RPC的使用

#### 1、启动

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



#### 2、配置文件



在启动之前，按需配置好配置文件。

配置文件为`根目录/config/brown.php`

配置文件如下：

```php
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
        'worker'=>[

        ],
        'worker_num'=>swoole_cpu_num(),
        'listen'=>[

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
    'trace'=>[
        'tracerUrl'=>'http://127.0.0.1:9411/api/v2/spans'
    ]
];

```



#### 3、快速使用

首先准备客户端和服务端两个项目

客户端和服务端框架可以不一样，

例如：服务端采用laravel，客户端采用tp6,



在`laravel`根目录中，执行·`composer require brown-rpc/brown-rpc` 安装以后需要进入至目录 `vendor/brown-rpc/brown-rpc/`,执行`composer run-script post-package-install`,返回至项目根目录中，执行`php brown rpc:run`.

第一次执行应该会出现异常，需要到`根目录/config.php`，将服务暴露，按照配置文件中的使用介绍来配置相关参数。



laravel配置文件如下：

```php
<?php

return [
    'framework' =>[
            'name'=>'laravel',
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
        'protocol'=>'http',
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
            'timeout'=>60,
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
    'trace'=>[
        'tracerUrl'=>'http://127.0.0.1:9411/api/v2/spans'
    ]


];

```



同步模式在Index.php中使用如下：

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

异步模式在Index.php中使用

```php
class Index extends BaseController
{
    public function index()
    {
		//面向对象回调函数
        echo (new RpcClient())->Sync(false)->callback([$this,'callback'])->Service('default')->request('Index')->hello([
            'name'=>111
        ]);
        
        //匿名函数回调函数
		(new RpcClient())->Sync(false)->callback(function($data){
            //回调函数 处理逻辑
        })->Service('default')->request('Index')
            ->hello([
            'name'=>111
        ]);
    }

    public function hello($name = 'ThinkPHP6')
    {
        return 'hello,' . $name;
    }

    public function callback($data){
        print_r($data);
        //处理逻辑
    }

}
```



在浏览器中访问的结果：

![image-20211228173541664](C:\Users\Administrator\AppData\Roaming\Typora\typora-user-images\image-20211228173541664.png)

#### 4、注册中心

注册中心默认使用`consul `  ,如果需要使用其他注册中心需要实现 `brown\register\RegisterInterface`接口,并将配置文件中的register.class替换为其他类名称。



默认不开启注册中心，如果开启注册中心会在启动服务的时候，将服务注册到consul注册中心.

可以访问http://127.0.0.1:8500/ui/dc1/services查看注册的服务



![image-20211228170257557](C:\Users\Administrator\AppData\Roaming\Typora\typora-user-images\image-20211228170257557.png)

![image-20211228165832581](C:\Users\Administrator\AppData\Roaming\Typora\typora-user-images\image-20211228165832581.png)



不开启注册中心，将会采用单机模式。

如果服务关闭，则自动会将服务注销。

使用`deRegister`可以将服务注销



#### 5、链路追踪



当服务越来越多的时候，有时候我们的服务端也可能变成客户端。每个服务可能需要很多个服务协同才能完成这个接口功能，如果链路上任何一个服务出现问题或者网络超时，都会形成导致接口调用失败。随着业务的不断扩张，服务之间互相调用会越来越复杂。那么就可能会登录各自的服务器去查看日志。链路追踪提供了完整的调用链过程，并提供了可视化的页面。



brown-rpc中默认使用`Zipkin`链路追踪，并可以查看调用的名称和层级。



默认访问http://127.0.0.1:9411/

![image-20211228171149067](C:\Users\Administrator\AppData\Roaming\Typora\typora-user-images\image-20211228171149067.png)







![image-20211228171159252](C:\Users\Administrator\AppData\Roaming\Typora\typora-user-images\image-20211228171159252.png)



### 2、队列的监听

队列中默认使用腾讯云CMQ。

#### 1、配置文件

在`根目录/config/brown.php`中

```php
'queue'=>[
        'enable'=>true,                    //开启队列监听
        'worker'=>[
                                           //队列监听实例
        ],
        'worker_num'=>swoole_cpu_num(),    //设置监听数量
        'listen'=>[
										   //设置监听队列的名
        ],
    ],
```

#### 2、队列实例

队列实例应该继承内置接口`QueueInterface`,并重写接口。建议使用`单例模式`。并在配置文件`worker`中加入如下：`Cmq::getInstance()`

例如：

```php
<?php
namespace app\controller;
use brown\cmq\Account;
use brown\cmq\CMQClientException;
use brown\cmq\CMQExceptionBase;
use brown\server\queue\QueueInterface;
use TencentCloud\Common\Credential;
use TencentCloud\Common\Exception\TencentCloudSDKException;
use TencentCloud\Common\Profile\ClientProfile;
use TencentCloud\Common\Profile\HttpProfile;
use TencentCloud\Tdmq\V20200217\Models\CreateCmqQueueRequest;
use TencentCloud\Tdmq\V20200217\Models\CreateCmqSubscribeRequest;
use TencentCloud\Tdmq\V20200217\Models\CreateCmqTopicRequest;
use TencentCloud\Tdmq\V20200217\Models\SendCmqMsgRequest;
use TencentCloud\Tdmq\V20200217\TdmqClient;


class Cmq implements QueueInterface
{

    protected $client;

    static private $instance;

    protected $isConnection;
    protected $secretId;
    protected $secretKey;
    protected $publicEndpoint;
    protected $mqPoint;
    protected $isInit;
    protected $region;
    static public function getInstance(){
        if (!self::$instance instanceof self) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    protected function init(){
        if (!$this->isInit){
            $cmq=config('cmq');
            $this->secretId=$cmq['secretId'];
            $this->region=$cmq['region'];
            $this->secretKey=$cmq['secretKey'];
            $this->publicEndpoint=$cmq['public_endpoint'];

            if (getenv('IS_ENV')=='dev'||empty(getenv('IS_ENV'))){
                $this->mqPoint=$cmq['debug_endpoint'];
            }else{
                $this->mqPoint=$cmq['vpc_endpoint'];
            }
        }
    }
    public function connection(array $options = [])
    {
        // TODO: Implement connection() method.
        $this->init();
        $cred = new Credential( $this->secretId, $this->secretKey);
        $httpProfile = new HttpProfile();
        $httpProfile->setEndpoint($this->publicEndpoint);

        $clientProfile = new ClientProfile();
        $clientProfile->setHttpProfile($httpProfile);
        // 创建tdmq客户端
        $this->client = new TdmqClient($cred, $this->region, $clientProfile);
        $this->isConnection=true;
        return $this;

    }

    public function createQueue($QueueName,$option=[])
    {
        try {
            $this->checkConnection();
            // TODO: Implement createQueue() method.
            $req =new CreateCmqQueueRequest();

            $params = array(
                "QueueName" => $QueueName,  // 消息队列名称
                "MaxMsgHeapNum"=>1000000,
                "PollingWaitSeconds"=>3,
                "Transaction"=>1
                // 以下是死信队列相关配置
//            "DeadLetterQueueName" => "dead_queue_api", // 死信队列名称，该队列需要优先创建
//            "Policy" => 0,  // 死信策略。0为消息被多次消费未删除，1为Time-To-Live过期
//            "MaxReceiveCount" => 3  // 最大接收次数 1-1000
                // MaxTimeToLive  policy为1时必选。最大未消费过期时间。范围300-43200，单位秒，需要小于消息最大保留时间msgRetentionSeconds
            );
            $req->fromJsonString(json_encode($params));

            $resp = $this->client->CreateCmqQueue($req);
            return $resp->QueueId;
        }catch (TencentCloudSDKException $exception){
            print_r($exception->getMessage().$exception->getFile().$exception->getLine());
            return $exception->getMessage();
        }

    }

    public function SendMessages($queueName,array $data,$DelaySeconds,$notifyUrl,$AutoAck='auto')
    {

        $this->checkConnection();
        $req=new SendCmqMsgRequest();
        if ($notifyUrl){
            $data=$data+['notifyUrl'=>$notifyUrl];
        }
        $data=$data+['AutoAck'=>$AutoAck];
        $params = array(
           "QueueName"=>$queueName,
           "MsgContent"=>json_encode($data,true),
           "DelaySeconds"=>$DelaySeconds,
        );

        $req->fromJsonString(json_encode($params));

        // 发送消息
        $resp = $this->client->SendCmqMsg($req);
        if ($resp->Result){
            return 1;
        }
    }

    public function SendBatchMessages()
    {
        // TODO: Implement SendBatchMessages() method.
    }

    public function account($queue_name,$type='queue'){
        $account=new Account($this->mqPoint,$this->secretId,$this->secretKey);
        if ($type=='queue'){
            return $account->get_queue($queue_name);
        }else{
            return $account->get_topic($queue_name);
        }

    }
    public function reciveMessage($queueName,  array $option = [])
    {
        // TODO: Implement reciveMessage() method.

        $this->checkConnection();
        try {
            $recv_msg = $this->account($queueName)->receive_message(3);
            return $recv_msg;
        } catch (CMQExceptionBase $e) {
            return $e;
        }

    }


    public function AcknowledgeMessage($queueName,$recv_msg ,array $option = [])
    {
        // TODO: Implement AcknowledgeMessage() method.

        try {
            // 获取消息
            // 消费成功，删除消息
            $this->account($queueName)->delete_message($recv_msg->receiptHandle);
        } catch (CMQExceptionBase $e) {
            echo "Create Queue Fail! Exception: " . $e;
            return;
        }
    }

    public function createTopic($TopicName, $FilterType, $MsgRetentionSeconds)
    {

        // TODO: Implement createTopic() method.
        $this->checkConnection();
        $req = new CreateCmqTopicRequest();
        $params = array(
            "TopicName" => $TopicName, // 主题名字，在单个地域同一帐号下唯一
            "FilterType" => $FilterType, // 用于指定主题的消息匹配策略。1：表示标签匹配策略；2：表示路由匹配策略
            "MsgRetentionSeconds" => $MsgRetentionSeconds // 消息保存时间。取值范围60 - 86400 s（即1分钟 - 1天）
        );
        $req->fromJsonString(json_encode($params));

        // 创建topic
        $resp = $this->client->CreateCmqTopic($req);
    }

    public function createSubscribe($TopicName, $SubscriptionName, $Protocol,$Type,array $TypeC,$url='')
    {
        try {
            $this->checkConnection();

            $req = new CreateCmqSubscribeRequest();

            if ($Protocol=='queue'){
                $NotifyContentFormat='SIMPLIFIED';
            }else{
                $NotifyContentFormat='JSON';
            }
            if ($Type=='tag'){
                $type=[
                    "FilterTag" => $TypeC
                ];
            }
            if ($Type=='route'){
                $type=[
                    "BindingKey" => $TypeC
                ];
            }
            $params = array(
                // 创建订阅的topic名称
                "TopicName" => $TopicName,
                // 订阅名称
                "SubscriptionName" => $SubscriptionName,
                // 订阅的协议，目前支持两种协议：http、queue。使用http协议，用户需自己搭建接受消息的web server。使用queue，消息会自动推送到CMQ queue，用户可以并发地拉取消息。
                "Protocol" => $Protocol,
                // 接收通知的Endpoint，根据协议Protocol区分：对于http，Endpoint必须以“http://”开头，host可以是域名或IP；对于Queue，则填QueueName。
                "Endpoint" => $url,
                // CMQ推送服务器的重试策略。取值有：1）BACKOFF_RETRY，退避重试。；2）EXPONENTIAL_DECAY_RETRY，指数衰退重试。
                "NotifyStrategy" => "BACKOFF_RETRY",
                // BindingKey数量不超过5个， 每个BindingKey长度不超过64字节，该字段表示订阅接收消息的过滤策略

                // 消息标签（用于消息过滤)。标签数量不能超过5个
                // "FilterTag" => array("TAG"),
                // 推送内容的格式。取值：1）JSON；2）SIMPLIFIED，即raw格式。如果Protocol是queue，则取值必须为SIMPLIFIED。如果Protocol是http，两个值均可以，默认值是JSON。
                "NotifyContentFormat" => $NotifyContentFormat
            );
            $params=($params+$type);
            $req->fromJsonString(json_encode($params));

// 创建订阅
            $resp = $this->client->CreateCmqSubscribe($req);
            print_r($resp);
            return $resp;
        }catch (CMQClientException $e){
            return $e;
        }

    }

    public function publishMessage($queue_name,$msg, array $tag, $route)
    {
        if (empty($tag)){
            return $this->account($queue_name,'topic')->publish_message($msg,[],$route);
        }else{

            return $this->account($queue_name,'topic')->publish_message($msg,$tag);

        }
    }

    private function checkConnection(){
        if (!$this->isConnection){
            $this->connection();
        }
    }
}
```

#### 3、监听队列

根据CMQ中队列名，直接在配置文件中`listen`中加入名称

4、启动

`php brown server:run`

```shell
[2022-10-11T14:31:44.391836+08:00] brown.INFO: 监听队列：yytc-product  [] []
[2022-10-11T14:31:44.394194+08:00] brown.INFO: 监听队列：yytc-order  [] []
[2022-10-11T14:31:47.566255+08:00] brown.INFO: 监听队列：yytc-order  [] []
[2022-10-11T14:31:47.591299+08:00] brown.INFO: 监听队列：yytc-product  [] []
```



### 3、一键生成RPC接口调用类与函数

#### 1、说明

为了更一步方便**客户端**调用，新增了`php brown rpcdoc:load` 命令,客户端配置好，服务端启动即可。

#### 2、使用

使用`php brown `查看

```sh
/var/www/rpc/client # php brown
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
  completion   Dump the shell completion script
  help         Display help for a command
  list         List commands
 rpc
  rpc:run      启动swoole服务
 rpcdoc
  rpcdoc:load  生成rpc server接口
 server
  server:run   启动swoole服务
```



使用`php brown rpcdoc:load`

```sh
/var/www/rpc/rpcclient # php brown rpc:load
生成中...sh: ipconfig: not found
创建目录...
正在生成命名空间...
正在创建接口MshTianyu
正在加载brown\RpcClient
正在创建接口name
正在为接口添加返回值 return (new RpcClient())->Service('msh')->request('Tianyu')->name([]);
正在创建接口age
正在为接口添加参数a
正在为接口添加参数a添加默认值1
正在为接口添加返回值a return (new RpcClient())->Service('msh')->request('Tianyu')->age(['a'=>$a,]);
创建成功MshTianyu.php
直接实例化调用 (new MshTianyu())

```

在项目中生成`rpc/服务名`目录，可以看到生成的php文件，以服务名+服务端暴露类名.php的文件

![image-20240314173102749](E:\学习\图床\image-20240314173102749-1710408932201-3.png)

调用

![image-20240314173210300](http://brown_sweet.gitee.io/images/rpc/image-20240314173210300.png)

## 软件安装



### Consul的安装



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



### Zipkin的安装



docker 安装Zipkin

```shell
$ docker pull openzipkin/zipkin
$ docker run --name zipkin -d -p 9411:9411 openzipkin/zipkin
```



## 联系作者

QQ：455764041



