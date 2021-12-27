<?php
/**
 *   Author:Brown
 *   Email: 455764041@qq.com
 *   Time: 2021/12/26 19:14
 */

namespace brown\register;
use brown\exceptions\RpcException;
use brown\server\core\Application;
use SensioLabs\Consul\ServiceFactory;
use SensioLabs\Consul\Services\Agent;
use SensioLabs\Consul\Services\AgentInterface as AgentInterfaceAlias;
use SensioLabs\Consul\Services\HealthInterface;

class Consul implements RegisterInterface
{
    use Application;
    protected $registerService;
    protected array $options;
    protected $enable=false;
    protected array $serviceCache
        = [
            'ttl'            => 10,
            'services'       => [],
            'lastUpdateTime' => 0,
        ];

    public function __construct($uri = 'http://127.0.0.1:8500', $options = [])
    {

        $this->options=$options;

        $this->registerService=new ServiceFactory([
            'base_uri'=>$uri
        ]);
    }

    function getName(): string
    {
        // TODO: Implement getName() method.
        return __CLASS__;
    }

    function register($service, $host, $port, $weight = 1)
    {

        $id = $host . '_' . $port;
        /** @var Agent $agent */
        $agent = $this->registerService->get(AgentInterfaceAlias::class);

        $agent->registerService([
            'ID'      => $id,
            'Name'    => $service,
            'Port'    => $port,
            'Address' => $host,
            'Tags'    => [
                'port_' . $port,
            ],
            'Weights' => [
                'Passing' => $weight,
                'Warning' => 1,
            ],
            'Check'   => [
                'TCP'                            => $host . ':' . $port,
                'Interval'                       => $this->options['interval'] ?? '10s',
                'Timeout'                        => $this->options['timeout'] ?? '5s',
                'DeregisterCriticalServiceAfter' => $this->options['deregisterCriticalServiceAfter'] ?? '30s',
            ],
        ]);
    }

    function unRegister($host, $port)
    {
        // TODO: Implement unRegister() method.
        $id = $host . '_' . $port;
        /** @var Agent $agent */
        $agent = $this->registerService->get(AgentInterfaceAlias::class);
        $agent->deregisterService($id);
    }

    function getServices(string $service): array
    {
        // TODO: Implement getServices() method.
        $cache = $this->serviceCache;
        $ttl = $this->options['ttl'] ?? $cache['ttl'];
        //本地缓存所有节点信息，避免每次请求都要从consul拉一遍数据
        if ($cache['lastUpdateTime'] + $ttl < time()) {

            $health = $this->registerService->get(HealthInterface::class);
            $servers = $health->service($service)->json();

            if (empty($servers)) {
                return [];
            }
            $result = [];
            foreach ($servers as $server) {
                $result[] = RegisterService::build($server['Service']['Address'], $server['Service']['Port'], $server['Service']['Weights']['Passing']);
            }
            $cache['service'] = $result;
            $cache['lastUpdateTime'] = time();
        }

        return $cache['service'];
    }

    function getRandomService(string $service): RegisterService
    {
        // TODO: Implement getRandomService() method.
        $services = $this->getServices($service);
        if (!$services) {
            throw new RpcException('It has not register module');
        }

        return $services[rand(0, count($services) - 1)];
    }

    function getWeightService(string $service): RegisterService
    {
        // TODO: Implement getWeightService() method.
        $serviceArr = [];
        $totalWeight = 0;
        $services = $this->getServices($service);
        if (!$services) {
            throw new RpcException('It has not register module');
        }

        /** @var RegisterService $service */
        foreach ($services as $service) {
            $totalWeight += $service->getWeight();
            $sort[] = $service->getWeight();
            $serviceArr[] = $service->toArray();
        }

        array_multisort($serviceArr, SORT_DESC, $sort);

        $start = 0;
        $rand = rand(1, $totalWeight);
        foreach ($serviceArr as $service) {
            if ($start + $service['weight'] >= $rand) {
                return RegisterService::build($service['host'], $service['port'], $service['weight']);
            }
            $start = $start + $service['weight'];
        }

    }
}