<?php
/**
 *   Author:Brown
 *   Email: 455764041@qq.com
 *   Time: 2021/12/19 19:23
 */
namespace brown\request;


use brown\tracer\TracerContext;

abstract class Request
{
    protected string $request;
    protected string $service;
    protected string $method;
    protected array $params;
    protected $file=[];
    protected bool $isSync = true; //是否同步请求，默认是
    protected bool $isSystem = false; //是否系统请求，默认否
    protected $error;
    protected ?TracerContext $traceContext;

    public static function create($service,$request,$method, $params, ?TracerContext $traceContext = null)
    {
        return new static ($service,$request,$method, $params, $traceContext);
    }

    public function __construct($service,$request,$method, $params, ?TracerContext $traceContext = null)
    {

        $this->service=$service;
        $this->request=$request;
        $this->method = $method;
        $this->params=$params;

        $this->traceContext = $traceContext;
        $this->init();
    }


    abstract public function init();



    public function getService(): string
    {
        return $this->service;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getParams(): array
    {
        return $this->params;
    }

    public function getRequest(){
       return $this->request;
    }
    public function setParams(array $params)
    {
        $this->params = $params;
    }

    public function setservice(string $name)
    {
        $this->service = $name;
    }

    public function mergeParams(array $params)
    {
        $this->params = array_merge($this->params, $params);
    }

    public function getTraceContext(): ?TracerContext
    {
        return $this->traceContext;
    }

    public function setTraceContext($traceID, $parentID, $url)
    {
        $this->traceContext = TracerContext::create($traceID, $parentID, $url);
    }

    public function setSync(bool $value)
    {
        $this->isSync = $value;
    }

    public function isSync(): bool
    {
        return $this->isSync;
    }

    public function setSystem(bool $value)
    {
        $this->isSystem = $value;
    }

    public function isSystem(): bool
    {
        return $this->isSystem;
    }

    public function getError()
    {
        return $this->error;
    }

    public function setError($err)
    {
        $this->error = $err;
    }

    /**
     * @return array
     */
    public function getFile(): array
    {
        return $this->file;
    }

    /**
     * @param array $file
     */
    public function setFile(array $file): void
    {
        $this->file = $file;
    }


}