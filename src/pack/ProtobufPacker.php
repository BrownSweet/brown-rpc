<?php

namespace brown\pack;

use brown\request\Request;
use BrownGrpc\Pack;
use RuntimeException;
//use brown\rpc\packer\Buffer;
//use brown\rpc\packer\File;

class ProtobufPacker implements PackInterface
{


    public function pack(Request $request, $type = self::TYPE_BUFFER)
    {
        $data = serialize($request);
        $serialize=(new Pack())->setValue($data);
        return $serialize->serializeToString();
    }


    public function unpack($data)
    {

        $stringMessage = new Pack();
        $stringMessage->mergeFromString($data);

// 从反序列化的对象中提取字符串值
        $deserializedString = $stringMessage->getValue();

        return unserialize($deserializedString);
    }



}
