#!/bin/sh

dt=`date '+%d%m%y_%H%M%S'`
log=logs/kecomex_automatic.log 
log1=logs/oracle_automatic.log

if [ "$3" == "ORACLE" ]
	then
		ssh 192.168.3.102 /root/shellpro/operacao/lib_usu/conect_prod-oradb.sh $1 $2 $3 | tee -a $log1
	else
		ssh 192.168.3.102 /root/shellpro/operacao/lib_usu/conect_prod-oradb.sh $1 $2 $3 > log_tmp_ecomex_$dt.log
   		cat log_tmp_ecomex_$dt.log | grep Data | head -2 | tail -1 | tee -a $log
    		rm -rf log_tmp_ecomex_$dt.log
fi

dthora=`date +%d-%m-%y'('%H:%M:%S')'`

echo "#################### Last Update $dthora (Brazil/Sao Paulo) ####################" > logs/logfinal.txt
echo " " >> logs/logfinal.txt

cat logs/oracle_automatic.log >> logs/logfinal.txt