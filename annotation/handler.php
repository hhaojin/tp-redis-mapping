<?php

namespace app\common\annotation\handler;

use app\common\annotation\mapping\RedisMapping;
use think\Request;
use think\Response;

return [
    /**
     * @param \ReflectionMethod $reflectionMethod
     * @param object $instance
     * @param RedisMapping $redisMapping
     */
    RedisMapping::class => function (\ReflectionMethod $reflectionMethod, $instance, RedisMapping $redisMapping) {
        if ($redisMapping->key) {
            $request = Request::instance();
            $extVars = [$request, Response::create()];
            $vars = $request->param();
            $uriParams = [];
            try {
                //获取反射参数
                $fparams = $reflectionMethod->getParameters();
                foreach ($fparams as $param) {
                    if (isset($vars[$param->getName()])) {
                        $uriParams[] = $vars[$param->getName()];
                    } else {
                        foreach ($extVars as $extVar) {
                            if (is_object($param->getType())) {
                                if ($param->getClass()->isInstance($extVar)) {
                                    $uriParams[] = $extVar;
                                }
                            }
                        }
                    }
                }
                //获取redis缓存
                $handler = new RedisHandler();
                $_key = $redisMapping->prefix . getKey($redisMapping->key, $vars);
                $func = $reflectionMethod->getClosure($instance);
                switch ($redisMapping->type) {
                    case $redisMapping::TYPE_STRING:
                        return $handler->redisByString($_key, $redisMapping, $uriParams, $func);
//                    case $redisMapping::TYPE_HASH:
//                        return $handler->redisByHash($_key, $redisMapping, $uriParams, $func);
                    default:
                        return false;
                }
            } catch (\Exception $e) {
                abort(0, $e->getMessage());
            }
        }
        return false;
    }
];

/**
 * @param $key
 * @param $params
 * @return mixed
 */
function getKey($key, $params)
{
    $pattern = "/^#(\w+)/i";
    if (preg_match($pattern, $key, $matches)) {
        return $params[$matches[1]];
    }
    return $key;
}