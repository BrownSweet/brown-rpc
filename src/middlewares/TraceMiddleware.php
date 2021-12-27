<?php
/**
 *   Author:Brown
 *   Email: 455764041@qq.com
 *   Time: 2021/12/21 19:01
 */
namespace brown\middlewares;


use Closure;
use brown\request\Request;
use brown\response\Response;
use Zipkin\Endpoint;
use Zipkin\Propagation\TraceContext;
use Zipkin\Reporters\Http;
use Zipkin\Samplers\BinarySampler;
use Zipkin\TracingBuilder;


class TraceMiddleware implements MiddlewareInterface
{
    function handle(Request $request, Closure $next): Response
    {

        $context = $request->getTraceContext();
        if (!$context) {
            return $next($request);
        }
        $traceContext = TraceContext::create($context->getTraceID(), $context->getParentID(), null, true);
        $endpoint = Endpoint::create($request->getService());
        $reporter = new Http(['endpoint_url' => $context->getReporterUrl()]);
        $sampler = BinarySampler::createAsAlwaysSample();
        $tracing = TracingBuilder::create()
            ->havingLocalEndpoint($endpoint)
            ->havingSampler($sampler)
            ->havingReporter($reporter)
            ->build();

        $tracer = $tracing->getTracer();
        $span = $tracer->newChild($traceContext);
        $span->setName($request->getMethod());
        $span->start();
        $span->tag('请求参数', serialize($request->getParams()));
        $request->setTraceContext($span->getContext()->getTraceId(), $span->getContext()
            ->getSpanId(), $context->getReporterUrl());

        $start = microtime(true);
        $result = $next($request);
        $end = microtime(true);

        $span->tag('响应状态码code', $result->code);
        $span->tag('响应提示语msg', $result->msg);
        $span->tag('响应耗时', $end - $start);
        $span->finish();
        $tracer->flush();
        return $result;
    }
}