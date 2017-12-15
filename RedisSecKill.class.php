<?php
// +----------------------------------------------------------------------
// | 
// +----------------------------------------------------------------------
// | Copyright (c) www.php63.cc All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 吹泡泡的鱼 <996674366@qq.com>
// +---------
class RedisSecKill
{
    /**
     * 是否启用阻塞模式，如果使用cli执行，建议开启阻塞模式
     * @var bool 是否使用阻塞模式，true是 false 否
     */
    public $block = true;

    /**
     * 当启用阻塞模式时该方法生效
     * @var int 超时时间，单位秒
     */
    public $timeOut = 100;

    /**
     * 为了避免类被重复实例化，第一次实例化后将会把实例化后的结果存入该方法
     * @var
     */
    private static $instance;


    /**
     * @var 配置项
     */
    private $config;
    private $redis;

    //初始化化类，防止被实例化
    private function __construct()
    {
        $this->redis = $this->connect();
    }

    //防止类被克隆
    private function __clone()
    {
        trigger_error('Clone is not allow!', E_USER_ERROR);
    }

    /**
     * 防止类重复实例化
     * 检测当前类是否已经实例化过，如果实例化过直接返回
     * @return redisEmail 返回实例化过后的对象
     */
    public static function getInstance()
    {
        //检测当前类是否实例化过
        if (!(self::$instance instanceof self)) {
            //如果当前类没有实例化过则实例化当前类
            self::$instance = new self;
        }
        return self::$instance;
    }

    //连接redis
    private function connect()
    {
        try {
            //引入配置文件
            $this->config = include 'config.php';
            $redis = new \Redis();
            $redis->pconnect($this->config['host'], $this->config['port']);
            return $redis;
        } catch (RedisException $e) {
            echo 'phpRedis扩展没有安装：' . $e->getMessage();
            exit;
        }
    }

    /**
     * 设置库存
     * @param $key key名称
     * @param $value 商品信息
     * @return bool 成功返回1 失败返回0
     */
    public function setInventory($key, $value)
    {
        return $this->redis->set($key, $value);
    }


    /**
     * 加入队列
     * 参数以数组方式传递，key为键名，value为要写入的值，value，如果需要写入多个则以数组方式传递
     * @param array 要加入队列的格式 ['key'=>'键名','value'=>[值]]
     * @return int 成功返回 1失败 返回0
     */
    public function joinQueue($key, $value)
    {
        return $this->redis->lpush($key, $value);
    }

    /**
     * @param $key
     * @return array|string
     */
    public function popQueue($key)
    {
        if ($this->block) {
            return $this->redis->brpop($key, $this->timeOut);
        } else {
            return $this->redis->rpop($key);
        }
    }

    /**
     * 获取key的总长度
     * @param $key 要获取的key
     * @return int 长度
     */
    public function getCount($key)
    {
        return $this->redis->llen($key);
    }

    /**
     * 删除指定的key
     * @param $key 要删除的key
     * @return int 成功返回受影响的行数
     */
    public function delKey($key)
    {
        return $this->redis->del($key);
    }

    /**
     * 检查指定key是否存在
     * @param string $key
     * @return bool 若key存在返回1 不存在返回0
     */
    public function exists($key = '')
    {
        return $this->redis->exists($this->prefix . $key);
    }

    /**
     * panicBuying 开始秒杀
     * @killKey 商品key
     * @data 用户信息
     **/
    public function panicBuying($inventoryKey, $data)
    {
        #开始抢购，检测库存
        $inventoryNumber = $this->redis->get($inventoryKey);
        if ($inventoryNumber == null || $inventoryNumber == 0) {
            return '-1';
        }
        //开启事务
        $this->redis->watch($inventoryKey);
        //事务开始
        $this->redis->multi();
        $this->redis->lPush('kill_goods',$data);
        $this->redis->DECR($inventoryKey);
        $result = $this->redis->exec();
        if ($result) {
            return 1;
        }
        return '-1';
    }
}