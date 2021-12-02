brown-rpc 是一款基于thinkphp6的rpc协议

brown-rpc项目特点：

1. `100%`开源
2. `100%`遵循composer标准
3. 支持tp6命令行
4. 支持webserver等

## 1、快速使用

## 项目安装

 ```
 composer create-project topthink/think 自定义项目名称
 
 cd 自定义项目名称/
 
 composer require brown-rpc/brown-rpc v2.0
 ```



## 项目使用

准备 client和server端项目均安装 brown-rpc

### server端

```
/
├── app                        项目目录
│   ├── controller             项目自带controller
│   ├── rpc   				   创建rpc目录（以下自己创建）
		├──interfaces          创建interfaces目录      
        	├──Pay.php		   创建Pay.php
        ├──service             创建service
        	├──Pay.php		   创建Pay.php
```



interfaces目录下Pay.php内容

```php
<?php

namespace app\rpc\interfaces;
interface Pay
{
    public function pay($para=[]);
}
```

service目录下Pay.php内容

```php
<?php
namespace app\rpc\service;
class Pay implements \app\rpc\interfaces\Pay
{
  public function pay($para = [])
  {

      return '支付成功';
  }
}
```

创建成功后



在config目录下找到swoole.php配置文件，找到rpc文件内容如下：

将service目录下创建的Pay写入配置文件services中，并将server中的enable设置为true开启服务

```php
'rpc'        => [
        'server' => [
            'enable'     => true,
            'host'       => '0.0.0.0',
            'port'       => 9004,
            'worker_num' => swoole_cpu_num(),
            'services'   => [
                \app\rpc\service\Pay::class
            ],
        ],
        'client' => [
        ],
    ],
```



在项目根目录中 php think brown:rpc 开启rpc服务



### client端

在client端config目录swoole.php配置文件中加入如下配置:

```php
'rpc'        => [
        'server' => [
            'enable'     => false,
            'host'       => '0.0.0.0',
            'port'       => 9000,
            'worker_num' => swoole_cpu_num(),
            'services'   => [
            ],
        ],
        'client' => [
            'Pay'=>[                       #服务名称
                'host'=>'127.0.0.1',       #服务ip
                'port'=>'9000'             #服务端口号
            ]
        ],
    ],
```



在client端项目根目录中使用 php think brown:rpc:interface

将自动在app目录中生成rpc.php 文件内容如下：

```php
<?php

/**
 * 自动生成接口文件，勿删
 */

declare(strict_types=1);
return [
    'Pay' =>   								#我们定义服务名称
   	[
        'request@Pay' => 					#服务中需要请求的接口
        	['method@pay' => 				#接口的名称
             	['parameter@para' =>        #需要的参数名称
                 	[	
                     	'type' => null, 
                        'nullable' => true
                    ]
                ]
            ]
    ]
];
```



在controller文件中Index.php中

```php
<?php
namespace app\controller;

use app\BaseController;
use brown\RpcClient;                      #引入客户端brown\RpcClient; 

class Index extends BaseController
{
    public function index()
    {

        return (new RpcClient())           #实例化Rpc客户端
            ->Service('Pay')               #service对应的为rpc.php中  服务名称
            ->request('WxPay')             #request中对应的为rpc.php  服务中需要请求的接口
            ->pay([                        #调用pay方法对应的为rpc.php 接口的名称
                'para'=>[                  #参数为para为rpc.php       参数名称
            	'appId'=>'456465'          
                ]
            ]);
    }

    public function hello($name = 'ThinkPHP6')
    {
        return 'hello,' . $name;
    }
}

```



至此rpc的服务端和客户端已经完成通信；



后续会加入注册中心等功能。