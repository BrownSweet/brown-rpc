<?php

/**
 *   Author:Brown
 *   Email: 455764041@qq.com
 *   Time: 2021/12/23 20:13
 */
namespace brown\server\rpc;
use brown\exceptions\RpcException;
use brown\request\Request;
use brown\response\Response;
use ReflectionClass;
use ReflectionMethod;
use Swoole\Coroutine\Server;

use Swoole\Coroutine\Server\Connection;
use Throwable;

trait RpcServer
{
    protected $serialization;
    /**加载rpc
     * @author Brown 2021/12/23 20:19
     */
    protected function loadRpcServer(){
        $workerNum = $this->getConfig('rpc.server.worker_num', swoole_cpu_num());
        $this->addMoreWorker($workerNum,[$this,'runRpcServer'],'rpc_server');
    }
    /**rpc最终运行的方法
     * @author Brown 2021/12/23 20:19
     */
    public function runRpcServer(){
        $this->bindRpcSerialization();
        $this->bindRpcService();

        $serialization=new $this->serialization($this->getConfig('parser'));

        $host = $this->getConfig('rpc.server.host', '0.0.0.0');
        $port = $this->getConfig('rpc.server.port', 9009);
        $protocol=$this->getConfig('rpc.protocol', 'http');

        if ($protocol=='http'||$protocol=='https'){

            $server = new \Swoole\Coroutine\Http\Server($host, $port,false,true);
            $server->set([
                'open_http2_protocol' => true,
                'open_http_protocol' => true,
            ]);
            $server->handle('/', function (\Swoole\Http\Request $request, \Swoole\Http\Response $response)use ($serialization) {
                $data = $request->rawContent();

//                die;
                $request=$serialization->unpack($data);
                $response->end(serialize($this->doRequest($request)));
            });
        }else{
            try {
                $server = new Server($host, $port, false, true);
            }catch (RpcException $exception){
                $this->deregister();
                throw new RpcException($exception->getMessage());
            }
            $server->set($this->options);
            $server->handle(function (Connection $conn)use($serialization) {
                while (true) {
                    //接收数据
                    $data = $conn->recv(1);
                    if ($data === '' || $data === false) {
                        $conn->close();
                        break;
                    }
                    $request=$serialization->unpack($data);

                    $conn->send(serialize($this->doRequest($request)));

                    $conn->close();
//                die;
                    //发送数据

                }
            });


        }
        $server->start();


    }

    public function doRequest(Request $request):Response{

        if (!$request instanceof Request){
            $response = Response::error('错误包头');
            return $response;
        }
        try {
            $handler = $this->getRequestHandler();
        }catch (\ReflectionException $e){
            return Response::error($e->getMessage());
        }
        $response=$handler($request);
        return  $response;
    }

    public function getRequestHandler()
    {
        return array_reduce(array_reverse($this->middlewares),
            function ($stack, $next) {
                return function ($request) use ($stack, $next) {
                    if ($next instanceof \Closure) {
                        return $next($request, $stack);
                    } elseif (is_string($next) && class_exists($next)) {
                        return (new $next())->handle($request, $stack);
                    } else {
                        return $next->handle($request, $stack);
                    }
                };
            }, function ($request) {
                return $this->call($request);
            });
    }

    public function call(Request $request): Response
    {

        if ($err = $request->getError()) {
            return Response::error($err);
        }
        $this->writeFile($request);
        if ($request->getRequest()!='rpc_doc'){
            $service = $this->services[$request->getService()][$request->getRequest()];

            if (!$service) {
                $this->logger->debug('service is not exist.', ['service' => $request->getService()]);
                return Response::error('service is not exist.');
            }

            $method=$request->getMethod();
            $reflect = new ReflectionClass($service['class']);
            $instance = $reflect->newInstanceArgs();
            if (!method_exists($instance, $method)) {
                $this->logger->debug('method is not exist.', ['method' => $request->getMethod()]);
                return Response::error(sprintf('%s method[%s] is not exist.', $service, $method));
            }


            $ctx = $request->getTraceContext();

            if ($ctx && method_exists($instance, 'setTracerContext')) {
                $instance->setTracerUrl($ctx->getReporterUrl())->setTracerContext($ctx);
            }

            try {
                $methodObj = new ReflectionMethod($reflect->getName(), $method);
                $result = $methodObj->invokeArgs($instance, $request->getParams());

            } catch (\Throwable $e) {

                return Response::error($e->getMessage());
            }
        }else{
            $service=$request->getParams()['service'];
            $result=$this->getInterfaces($service);

        }

        return Response::success([
            'result' => $result
        ]);
    }

    public function writeFile(Request $request){
        $params=$request->getParams();
        foreach ($request->getFile() as $index=>$value) {
            $name = tempnam(sys_get_temp_dir(), 'swoole_rpc_');
            $handle = fopen($name, 'ab');
            fwrite($handle, $value);
            $params['file'][$index]=$name;
        }
        $request->setParams($params);
    }
    /**
     * 获取接口信息
     * @return array
     */
    protected function getInterfaces($service)
    {
        $interfaces = [];
        foreach ($this->services[$service] as $key => ['interface' =>$interface]) {
            $interfaces[$key] = $this->getMethods($interface);

        }
        return $interfaces;
    }

    protected function getMethods($interface)
    {
        $methods = [];

        $reflection = new \ReflectionClass($interface);

        foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            if ($method->isConstructor() || $method->isDestructor()) {
                continue;
            }
            $returnType = $method->getReturnType();
            if ($returnType instanceof \ReflectionNamedType) {
                $returnType = $returnType->getName();
            }

            $methods[$method->getName()] = [
                'parameters' => $this->getParameters($method),
                'returnType' => $returnType,
                'comment'    => $method->getDocComment(),
            ];
        }
        return $methods;
    }

    protected function getParameters(ReflectionMethod $method)
    {
        $parameters = [];
        foreach ($method->getParameters() as $parameter) {
            $type = $parameter->getType();
            if ($type instanceof \ReflectionNamedType) {
                $type = $type->getName();
            }

            $param = [
                'name' => $parameter->getName(),
                'type' => $type,
            ];

            if ($parameter->isOptional()) {
                $param['default'] = $parameter->getDefaultValue();
            }

            if ($parameter->allowsNull()) {
                $param['nullable'] = true;
            }

            $parameters[] = $param;
        }
        return $parameters;
    }

}