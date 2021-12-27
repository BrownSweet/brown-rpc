<?php
/**
 *   Author:Brown
 *   Email: 455764041@qq.com
 *   Time: 2021/12/21 19:01
 */
namespace brown\middlewares;


use Closure;
use brown\Request\Request;
use brown\response\Response;


interface MiddlewareInterface
{
    function handle(Request $request, Closure $next): Response;
}