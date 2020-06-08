# tp5-redisMapping

#### 介绍
借鉴swoft的注解想法，在自己项目中改造了一下，引入了一个redis注解功能，可以实现无侵入加上redis缓存

#### 软件架构
基于thinkphp5.0实现的功能，在框架行为驱动的app_begin的时候解析目标方法的注释，从而实现注解功能


#### 安装教程

1.  把目录克隆到 application/common 目录下。 (ide最好下载一个annotation插件，不然没有代码提示)
2.  在application/tags.php里面添加驱动
3.  引入一个注解解析包：composer require doctrine/annotations 1.4.0  （因为本项目是基于php7.0的,所以选择了低版本）

```
    'action_begin'    => [
        'app\\common\\behavior\\Annotation',
    ],
```

#### 使用说明

1.  在控制器方法上面写上注释即可
    属性说明：
    1.  key 是会匹配 input() 方法里面的值，例如 input("index")=1 ,那么属性key的值就是1
    2.  prefix 自定义的key前缀
    3.  expries key 的过期时间
    4.  type 存储类型，常量 RedisMapping::TYPE_STRING （目前这个demo只写了string类型的）
2.  几个需要改动的地方
    1.  app\common\annotation\handler\RedisHandler.php 的构造方法里面连接redis的方法，请根据自己项目自行改动
    2.  app\common\annotation\handler\RedisHandler.php 第40行，这里我结合自己的项目统一状态码判断了是否需要缓存，您可以根据自己需要改动
    
```
namespace app\api\controller;

class Index extends Controller {

    /**
     * @RedisMapping(key="index",prefix="test_",expries="20",type=RedisMapping::TYPE_STRING)
     */
     public function index(){ 
        //todo
     }
     
}
```
