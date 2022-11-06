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
        $listen = $this->getConfig('queue.listen');
        $workerNum = $this->getConfig('queue.worker_num');
        foreach ($workers as $worker) {
            foreach ($listen as $listen) {
                $this->addMoreWorker($workerNum, function (Process\Pool $pool) use ($worker, $listen) {
                    $this->logger->info('监听队列：' . $listen . PHP_EOL);
                    $queueHandle = $worker->reciveMessage($listen);
                    while ($queueHandle) {
                        if (!$queueHandle instanceof CMQExceptionBase) {
                            $cid = Coroutine::create(function () use ($listen, $queueHandle, $worker) {
                                $msgBody = json_decode($queueHandle->msgBody, true);
                                $autoAck = $msgBody['AutoAck'];
                                if (isset($msgBody['notifyUrl'])) {
                                    $url = $msgBody['notifyUrl'];
                                    unset($msgBody['notifyUrl']);
                                    unset($msgBody['AutoAck']);
                                    $data = json_encode($msgBody);
                                    $header = [
                                        'user-agent' => 'Send Notification Brwon Queue Service Agent',
                                        'content-type' => 'text/plain; charset=utf-8',
                                        'x-cmq-message-id' => $queueHandle->msgId,
                                        'x-cmq-receipt-handle' => $queueHandle->receiptHandle,
                                        'x-cmq-enqueue-time' => $queueHandle->enqueueTime,
                                        'x-cmq-queue-name' => $listen,
                                        'x-cmq-queue-ack' => $autoAck
                                    ];
                                    $this->logger->info('开启处理协程,协程的父Id：' . Coroutine::getPcid() . ' 协程的Id' . Coroutine::getCid());
                                    $this->logger->info($queueHandle . PHP_EOL);
                                    $code = SendRequest($url, $data, $header);
                                    if ($autoAck == QueueConstant::AUTO_ACK) {
                                        $worker->AcknowledgeMessage($listen, $queueHandle);
                                    }
                                    if ($autoAck == QueueConstant::STATUS_CODE_ACK) {
                                        if ($code >= 200 && $code < 400) {
                                            $worker->AcknowledgeMessage($listen, $queueHandle);
                                        }
                                    }
                                    if ($autoAck == QueueConstant::MANNUL_ACK) {

                                    }
                                }
                            });
                            $this->logger->info('内存使用量：' . Coroutine::getStackUsage());

                        } else {

                        }
                    }
                },'queue_server');
            }

        }


    }
}
