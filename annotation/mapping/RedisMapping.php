<?php

namespace app\common\annotation\mapping;

use Doctrine\Common\Annotations\Annotation\Target;

/**
 * @Annotation()
 * @Target({"METHOD"})
 */
class RedisMapping
{
    const TYPE_STRING = 'string';
//    const TYPE_HASH = 'hash';

    public $source = 'default';
    public $type = '';//存储类型
    public $prefix = '';//key前缀
    public $key = '';
    public $expries = 0;//过期时间
    public $incryKey = 0;//hash自增
}
