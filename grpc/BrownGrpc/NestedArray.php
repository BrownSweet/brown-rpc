<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: protobuf_pack.proto

namespace BrownGrpc;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Generated from protobuf message <code>brownGrpc.NestedArray</code>
 */
class NestedArray extends \Google\Protobuf\Internal\Message
{
    /**
     * Generated from protobuf field <code>repeated .brownGrpc.NestedArray.InnerArray arrays = 1;</code>
     */
    private $arrays;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type array<\BrownGrpc\NestedArray\InnerArray>|\Google\Protobuf\Internal\RepeatedField $arrays
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\ProtobufPack::initOnce();
        parent::__construct($data);
    }

    /**
     * Generated from protobuf field <code>repeated .brownGrpc.NestedArray.InnerArray arrays = 1;</code>
     * @return \Google\Protobuf\Internal\RepeatedField
     */
    public function getArrays()
    {
        return $this->arrays;
    }

    /**
     * Generated from protobuf field <code>repeated .brownGrpc.NestedArray.InnerArray arrays = 1;</code>
     * @param array<\BrownGrpc\NestedArray\InnerArray>|\Google\Protobuf\Internal\RepeatedField $var
     * @return $this
     */
    public function setArrays($var)
    {
        $arr = GPBUtil::checkRepeatedField($var, \Google\Protobuf\Internal\GPBType::MESSAGE, \BrownGrpc\NestedArray\InnerArray::class);
        $this->arrays = $arr;

        return $this;
    }

}

