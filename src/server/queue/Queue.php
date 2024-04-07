<?php
namespace brown\server\queue;

use app\controller\Client;
use app\controller\Cmq;
use brown\cmq\Account;
use brown\cmq\CMQExceptionBase;
use Swoole\Coroutine;
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

    protected function runQueueServer ()
    {

        $workers = $this->getConfig('queue.worker');
        $listens = $this->getConfig('queue.listen');
        $workerNum = $this->getConfig('queue.worker_num');
        $handlers=$this->getConfig('queue.handlers');
        $ack=$this->getConfig('queue.ack');

        foreach ($workers as $worker) {
            foreach ($listens as $listen) {
                $this->addMoreWorker($workerNum, function (Process\Pool $pool) use ($workers, $listen,$handlers,$ack) {
                    $this->logger->info('监听队列：' . $listen . PHP_EOL);
                    while (true) {
                        $queueHandle = $workers[$listen]->reciveMessage($listen);
                        foreach ($handlers[$listen] as $handler){

                            $handler_obj=new $handler();
                            if ($handler_obj instanceof QueueHandler){
                                $cid = Coroutine::create(function () use ($listen, $queueHandle, $workers,$handler_obj) {
                                    $handler_obj->handle($listen, $queueHandle);
                                });
                            }else{
                                $handler_obj=null;
                            }
                        }

                        if (array_key_exists($listen,$ack)){
                            foreach ($ack[$listen] as $ackItem){
                                $ackItem_obj=new $ackItem();
                                if ($ackItem_obj instanceof QueueAcknowledge){
                                    $cid = Coroutine::create(function () use ($listen, $queueHandle, $workers,$ackItem_obj) {
                                        $ackItem_obj->AcknowledgeMessage($listen, $queueHandle);
                                    });
                                }else{
                                    $ackItem_obj=null;
                                }
                            }
                        }
                    }
                },'queue_server');
            }

        }


    }
}
