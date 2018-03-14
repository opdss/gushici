#!/bin/bash

# 一个命令创建数据库和账号密码
#参数传入数据库名称就好

ms=`which mysql`
log="$0.log"

dbRootPass='X7SBMixvFKLKUIL*a@cWX'

randstr() {
  	n=$1
	expr $n + 0 &>/dev/null
	if [ $? != 0 ];then
		n=24
	fi
	index=0
  	str=""
  	for i in {a..z}; do arr[index]=$i; index=`expr ${index} + 1`; done
  	for i in {A..Z}; do arr[index]=$i; index=`expr ${index} + 1`; done
  	for i in {0..9}; do arr[index]=$i; index=`expr ${index} + 1`; done
	sssss='~ @ # $ % ^ &  ( ) _ + < ? >  [ ] , : { } -/ '
	for i in $sssss; do arr[index]=$i; index=`expr ${index} + 1`; done
  	a=1
	while [ $a -le $n ]; do
  		str="$str${arr[$RANDOM%$index]}"
  		let a++
  	done
 	echo $str
}

db=$1
user=$db
region='%'
password=`randstr 16`

createDB="create database $db DEFAULT CHARSET utf8 COLLATE utf8_general_ci;"
createUser="create user '$user'@'$region' identified by '$password';"
grantsr="grant all privileges on $db.* to '$user'@'$region';"


$ms -uroot -p"$dbRootPass" -e "$createUser;flush privileges;"
if [ $? -ne 0 ];
then
    echo 'create user error!'
    exit
fi

$ms -uroot -p"$dbRootPass" -e "$createDB;$grantsr;flush privileges;"
if [ $? -eq 0 ];
then
    echo '-----------------------------------' >>  $log
    msg="db:$db, user:$user@$region, password:$password"
    echo $msg >> $log
    echo "$createDB" >> $log
    echo "$createUser" >> $log
    echo "$grantsr" >> $log
    echo $msg
fi
