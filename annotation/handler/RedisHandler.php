<?php
/**
 * Created by PhpStorm.
 * User: haojin
 * Date: 2020/4/16
 * Time: 16:26
 */

namespace app\common\annotation\handler;

use app\common\annotation\mapping\RedisMapping;

vendor('redis.redis');

class RedisHandler
{
    protected $redis;

    public function __construct()
    {
        $this->redis = \MyRedis::getInstance()->getRedis();
    }

    /**
     * string类型
     * @param string $_key
     * @param RedisMapping $redisMapping
     * @param array $params
     * @param callable $func
     * @return bool|mixed|string
     */
    function redisByString(string $_key, RedisMapping $redisMapping, array $params, callable $func)
    {
        $getData = $this->redis->get($_key);
        //缓存如果有，直接返回,缓存没有，则直接执行原控制器方法，并返回
        if ($getData) {
            return json_decode($getData, true);
        } else {
            $getData = call_user_func($func, ...$params);
            if ($getData && $getData['code'] != '0') { //判断是否正常状态码
                if ($redisMapping->expries > 0) {
                    $this->redis->setex($_key, (int)$redisMapping->expries, json_encode($getData, true));
                } else {
                    $this->redis->set($_key, json_encode($getData, true));
                }
            }
            return $getData;
        }
    }

    /**
     * 哈希类型
     * @param string $_key
     * @param RedisMapping $redisMapping
     * @param array $params
     * @param callable $func
     * @return array|mixed
     * @throws \Exception
     */
//    function redisByHash(string $_key, RedisMapping $redisMapping, array $params, callable $func)
//    {
//        if ($redisMapping->incryKey) {
//            $this->redis->hIncrBy($_key, $redisMapping->incryKey, 1);
//        }
//        $getData = $this->redis->hGetAll($_key);
//        if ($getData) {
//            return $getData;
//        } else {
//            $getData = call_user_func($func, ...$params);
//            if (is_object($getData)) {
//                $getData = json_decode(json_encode($getData, true), true);
//            }
//            if (!is_array($getData)) {
//                throw new \Exception("data must be array");
//            }
//            $this->redis->hMset($_key, $getData);
//            return $getData;
//        }
//    }

}