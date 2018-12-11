#!/bin/sh
PATH=/usr/local/nginx/sbin:/usr/local/php/bin:/usr/local/mysql/bin:$PATH
#
db_host=$1
db_user=$2
db_pwd=$3
client_db_name=$4
client_db_user=$5
client_db_pwd=$6
sql_tpl=$7

# 创建数据库并分配用户
mysql_exe=`which mysql`
grant_sql="GRANT SELECT, EXECUTE, DELETE, INSERT, UPDATE  ON ${client_db_name}.* TO ${client_db_user}@'%';"
exec_sql="CREATE DATABASE ${client_db_name} DEFAULT CHARSET 'utf8mb4' COLLATE 'utf8mb4_unicode_ci'; ${grant_sql}"
init_cmd=`${mysql_exe} -h${db_host} -u${db_user} -p${db_pwd} -e "${exec_sql}" 2>&1 | grep -v 'insecure' ; echo inited`
if [ "$init_cmd" != "inited" ];then
    >&2 echo $init_cmd
    exit 1
fi
#
# 使用 client 帐号导入SQL文件语句并执行初始化数据库对象
import_cmd=`${mysql_exe} -h${db_host} -u${client_db_user} -p${client_db_pwd} ${client_db_name} < ${sql_tpl} 2>&1 | grep -v 'insecure'; echo imported`
if [ "$import_cmd" != "imported" ];then
    >&2 echo $import_cmd
    exit 1
fi
##
echo ok
