<?php

/**
 *   Author:Brown
 *   Email: 455764041@qq.com
 *   Time: 2021/12/26 19:09
 */
namespace brown\register;
interface RegisterInterface
{
   function getName():string;

    function register($module, $host, $port, $weight = 1);

    function unRegister($host, $port);

    function getServices(string $service): array;

    function getRandomService(string $service): RegisterService;

    function getWeightService(string $service): RegisterService;
}