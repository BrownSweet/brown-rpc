<?php

namespace brown\pack;

use brown\request\Request;
use RuntimeException;
//use brown\rpc\packer\Buffer;
//use brown\rpc\packer\File;

class Packer implements PackInterface
{


    public function pack(Request $request, $type = self::TYPE_BUFFER)
    {
        $data = serialize($request);
        return pack(self::HEADER_PACK, strlen($data), $type) . $data;
    }


    public function unpack($data)
    {
        $header = unpack(self::HEADER_STRUCT, substr($data, 0, self::HEADER_SIZE));
        if ($header === false) {
            throw new RuntimeException('Invalid Header');
        }



        $data = substr($data, self::HEADER_SIZE);

        return unserialize($data);
    }



}
