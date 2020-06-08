<?php
/**
 * Created by PhpStorm.
 * User: haojin
 * Date: 2020/4/15
 * Time: 21:33
 */

namespace app\common\behavior;


use app\common\annotation\mapping\RedisMapping;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use think\Config;
use think\Hook;
use think\Log;
use think\Response;

class Annotation
{
    protected static $handllers = [];

    /**
     * @param array $params
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws \ReflectionException
     */
    public function run($params)
    {
        AnnotationRegistry::registerLoader('think\\Loader::autoload');
        $this->getAnnotationHandler(APP_PATH . "common/annotation");
        if (empty(self::$handllers)) {
            return false;
        }
        $class = get_class($params[0]);
        $ref = new \ReflectionClass($class);
        $reader = new AnnotationReader();
        try {
            $refMethod = $ref->getMethod($params[1]);
            $annotations = $reader->getMethodAnnotations($refMethod);
            foreach ($annotations as $annotation) {
                $className = get_class($annotation);
                //暂时只搞redis注解，后续有需要再改
                if (isset(self::$handllers[$className]) && $className === RedisMapping::class) {
                    $handler = self::$handllers[$className];
                    $cacheData = call_user_func_array($handler, [$refMethod, $ref->newInstance(), $annotation]);
                    $this->response($cacheData);
                }
            }
        } catch (\Exception $e) {
            $msg = [
                'code' => $e->getCode(),
                'msg' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ];
            Log::write(json_encode($msg, true), 'mapping');
        }
        return true;
    }

    /**
     * 加载处理注解的handler
     * @param string $dir
     */
    private function getAnnotationHandler(string $dir)
    {
        $handlerFiles = glob($dir . "/*.php");
        foreach ($handlerFiles as $handlerFile) {
            $handler = require $handlerFile;
            self::$handllers = array_merge(self::$handllers, $handler);
        }
    }

    /**
     * 响应数据
     * @param $cacheData
     */
    private function response($cacheData)
    {
        if ($cacheData) {
            // 输出数据到客户端
            if ($cacheData instanceof Response) {
                $response = $cacheData;
            } elseif (!is_null($cacheData)) {
                // 默认自动识别响应输出类型
                $type = Config::get('redis_mapping_return_type') ?: 'json'; //redis注解默认返回json格式
                $response = Response::create($cacheData, $type);
            } else {
                $response = Response::create();
            }
            Hook::listen('app_end', $response);
            $response->send();
            exit();
        }
    }

}