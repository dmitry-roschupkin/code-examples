#!/bin/bash

baseDir=/var/www/backup_monitor

cd $baseDir

result=`php yiic $1`

#echo $result

zabbix_sender -z 10.128.0.6 -s mirror.project.com -k $1 -o $result

