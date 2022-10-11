<?php
namespace brown\server\queue;

use app\controller\Client;
use app\controller\Cmq;
use brown\cmq\Account;
use brown\cmq\CMQExceptionBase;
use Swoole\Process;
use TencentCloud\Common\Credential;
use TencentCloud\Common\Exception\TencentCloudSDKException;
use TencentCloud\Common\Profile\ClientProfile;
use TencentCloud\Common\Profile\HttpProfile;
use TencentCloud\Tdmq\V20200217\Models\AcknowledgeMessageRequest;
use TencentCloud\Tdmq\V20200217\Models\ReceiveMessageRequest;
use TencentCloud\Tdmq\V20200217\TdmqClient;

trait Queue{

    protected $serialization;
    /**加载rpc
     * @author Brown 2021/12/23 20:19
     */
    protected $client;
    protected function loadQueueServer(){
        if ($this->getConfig('queue.enable')){
            $this->runQueueServer();
        }
    }

    protected function runQueueServer ()  {

        $workers=$this->getConfig('queue.worker');
        $listen=$this->getConfig('queue.listen');
        $workerNum=$this->getConfig('queue.worker_num');
        foreach ($workers as $worker){
            foreach ($listen as $listen){
                $this->addMoreWorker($workerNum,function (Process\Pool $pool) use($worker,$listen){
                    $this->logger->info('监听队列：'.$listen.PHP_EOL);
                    $queueHandle=$worker->reciveMessage($listen);

                    if (!$queueHandle instanceof CMQExceptionBase){
                        $this->logger->info($queueHandle.PHP_EOL);
                        $msgBody=json_decode($queueHandle->msgBody,true);
                        $autoAck=$msgBody['AutoAck'];
                        if (isset($msgBody['notifyUrl'])){
                            $url=$msgBody['notifyUrl'];
                            unset($msgBody['notifyUrl']);
                            unset($msgBody['AutoAck']);
                            $code=SendRequest($url,json_encode($msgBody),[
                                'user-agent'   => 'Send Notification Brwon Queue Service Agent',
                                'content-type' => 'text/plain; charset=utf-8',
                                'x-cmq-message-id'=>$queueHandle->msgId,
                                'x-cmq-receipt-handle'=>$queueHandle->receiptHandle,
                                'x-cmq-enqueue-time'=>$queueHandle->enqueueTime,
                                'x-cmq-queue-name'=>$listen,
                                'x-cmq-queue-ack'=>$autoAck
                            ]);
                        }

                        if ($autoAck==QueueConstant::AUTO_ACK){
                            $worker->AcknowledgeMessage($listen,$queueHandle);
                        }
                        if ($autoAck==QueueConstant::STATUS_CODE_ACK){
                            if ($code>=200&&$code<400){
                                $worker->AcknowledgeMessage($listen,$queueHandle);
                            }
                        }
                        if ($autoAck==QueueConstant::MANNUL_ACK){

                        }
                    }else{

                    }
                });
            }

        }

    }
}
