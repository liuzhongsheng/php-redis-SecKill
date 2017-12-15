<?php
#实现思路
#添加商品时将商品数量写入缓存，购买时直接扣除redis里的库存，避免直接请求数据库，造成较大压力
#用户抢购时，按顺序写入队列，然后用服务器执行，避免直接对数据库造成压力
#最后根据redis里保存的信息，完成订单信息

 ini_set('display_errors', true);

  error_reporting(E_ALL);

#定义用户id、库存总数和商品id后期通过程序获得
$goods_id = 1;
$user_id = rand(1,99999);
$order_number = 'KO2018';
$total='1.00';
$order_time=time();
#定义key的名称
$key = 'goods_'.$goods_id;

#引入类
require 'RedisSecKill.class.php';
$object = RedisSecKill::getInstance();
$data = [$goods_id, $user_id, '\''.$order_number.'\'', $total, $order_time];
echo $object->panicBuying($key, implode(',', $data));
exit;
#模拟添加商品，以json方式写入
#需要写入redis的数据：商品id,库存，可根据需求自行添加
$object->setInventory($key, 10);
exit;
#开始抢购，检测库存
// $inventoryData = $object->getInventory($key);

#检测是否存在
// if ($inventoryData == null) {
//     echo '抱歉商品已卖完...';
//     exit;
// }

#解析json
// $inventoryData = json_decode($inventoryData, true);
// #检测库存
// if ($inventoryData['inventory'] == 0) {
//     echo '抱歉商品已卖完...';
//     exit;
// }

#将抢购信息写入队列
// $data = ['user_id'=>$userId, 'goods_id'=>$goodsId];
// $result = $object->joinQueue(['key'=>'kill_goods_'.$goodsId, ['value'=>json_encode($data)]]);
// #减少库存
// if ($result) {
//     $goodsData = ['id'=>$goodsId,'inventory'=>$inventoryData['inventory']-1];
//     $object->setInventory($key, json_encode($goodsData));
//     file_put_contents('1.txt',($inventoryData['inventory']-1).PHP_EOL,FILE_APPEND);
//     exit;
// }
// echo '抱歉商品已卖完...';
// exit;