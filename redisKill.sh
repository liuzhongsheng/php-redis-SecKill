#!/bin/sh
#redis地址
redis_host="127.0.0.1"

#redis端口
redis_port=6379

#redis密码
redis_pwd=

#mysql连接地址
mysql_host="localhost"

#mysql账号
mysql_user="root"

#mysql密码
mysql_pwd="qwertyuiop"

#数据库名称
mysql_db_name="order"

#数据库表名
mysql_table_name="order"

#mysql字段，和写入时的顺序一致
mysql_field="goods_id, user_id, order_number, total, order_time"
#检测redis密码是否为空
if [ ! -z "${redis_pwd}" ];then
    redis_pwd="-a ${reds_pwd}"
fi

#连接redis
data=`redis-cli -h ${redis_host} -p ${redis_port} ${redis_pwd} rpop kill_goods`

#如果结果为空退出脚本
if [ !{$data} ];then
   echo "任务结束..."
  exit;
fi

#组装sql
sql="insert into ${mysql_db_name}.${mysql_table_name}(${mysql_field}) values(${data});"

#添加订单
`/Applications/MAMP/Library/bin/mysql -h${mysql_host} -u${mysql_user} -p${mysql_pwd} -e "${sql}"`

