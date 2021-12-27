<?php


/**
 *   Author:Brown
 *   Email: 455764041@qq.com
 *   Time: 2021/12/23 21:06
 */
namespace brown\pack;
interface PackInterface
{
    public const HEADER_SIZE   = 8;
    public const HEADER_STRUCT = 'Nlength/Ntype';
    public const HEADER_PACK   = 'NN';
    public const TYPE_BUFFER =0;
    public const TYPE_FILE =1;


    function pack(\brown\request\Request $request);
    function unpack(string $data);
}