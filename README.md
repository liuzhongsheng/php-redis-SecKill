# php-redis-secKill
<h3>使用说明：</h3>
<pre>
//实现思路
//添加商品时将商品数量写入缓存，购买时直接扣除redis里的库存，避免直接请求数据库，造成较大压力
//用户抢购时，按顺序写入队列，然后用服务器执行，避免直接对数据库造成压力
//最后根据redis里保存的信息，完成订单信息
</pre>
<p>使用示例：</p>
<pre>加入队列示例
    require APP_PATH.'/plugin/RedisEmail/RedisEmail.class.php';
      $goods_id = '商品id';
      $user_id = '用户id';
      $order_number = '订单号';
      $total='加个';
      $order_time=time();
      定义key的名称
      $key = 'goods_'.$goods_id;
      $object = RedisSecKill::getInstance();
 
      添加商品，写入库存
      $object->setInventory($key, 10);
      
      实现抢购
      $data = [$goods_id, $user_id, '\''.$order_number.'\'', $total, $order_time];
      $object->panicBuying($key, implode(',', $data));
      
      抢购成功返回1，抢购失败返回-1
</pre>

</pre>
<pre>shell：
#!/bin/sh
#redis地址
redis_host="127.0.0.1"

redis端口
redis_port=6379

redis密码
redis_pwd=

mysql连接地址
mysql_host="localhost"

mysql账号
mysql_user="root"

mysql密码
mysql_pwd="qwertyuiop"

数据库名称
mysql_db_name="order"

数据库表名
mysql_table_name="order"

mysql字段，和写入时的顺序一致
mysql_field="goods_id, user_id, order_number, total, order_time"
检测redis密码是否为空
if [ ! -z "${redis_pwd}" ];then
    redis_pwd="-a ${reds_pwd}"
fi

连接redis
data=`redis-cli -h ${redis_host} -p ${redis_port} ${redis_pwd} rpop kill_goods`

如果结果为空退出脚本
if [ !{$data} ];then
   echo "任务结束..."
  exit;
fi

组装sql
sql="insert into ${mysql_db_name}.${mysql_table_name}(${mysql_field}) values(${data});"

添加订单
`/Applications/MAMP/Library/bin/mysql -h${mysql_host} -u${mysql_user} -p${mysql_pwd} -e "${sql}"`


</pre>

<h3>配置说明<span>(参考config.php)</span>：</h3>

<p>config.php<p>
<pre>

return [
    'start_using'       =>  'off',  //on 开 off关闭
    'host'              =>  '127.0.0.1',    //服务地址
    'port'              =>  6379,   //服务端口号
</pre>


<p>以上为本程序使用方式欢迎大家提提建议或者加入QQ群：456605791 交流，如果觉得代码写得还行请赞一个谢谢,欢迎提出更好的解决办法<p>
<b>url:<a href='https://www.php63.cc'>https://www.php63.cc</a></b>