<?php

namespace brown\server\queue;


use brown\cmq\Account;
use brown\cmq\CMQClientException;
use brown\cmq\CMQExceptionBase;
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