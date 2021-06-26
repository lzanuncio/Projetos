#!/bin/sh

dt=`date '+%d%m%y_%H%M%S'`
log=logs/ecomex/kecomex_automatic.log 
log1=logs/oracle/oracle_automatic.log

dthora=`date +%d-%m-%y'('%H:%M:%S')'`

if [ "$3" == "ECOMEX" ]
	then
		ssh 192.168.4.102 /root/shellpro/operacao/lib_usu/conect_prod-oradb2.sh $1 $2 $3 > log_tmp_ecomex_$dt.log
   		cat log_tmp_ecomex_$dt.log | grep Data | head -2 | tail -1 | tee -a $log
    	rm -rf log_tmp_ecomex_$dt.log	
	else
		ssh 192.168.4.102 /root/shellpro/operacao/lib_usu/conect_prod-oradb2.sh $1 $2 $3 | tee -a $log1
fi

		echo "#################### Last Update $dthora (Brazil/Sao Paulo) ####################" > logs/oracle/logfinal.txt
		echo "#################### Last Update $dthora (Brazil/Sao Paulo) ####################" > logs/ecomex/logfinal.txt
		
		echo "############################## Numero total de Visitas: $4 ###############################" >> logs/oracle/logfinal.txt
		echo "############################## Numero total de Visitas: $4 ###############################" >> logs/ecomex/logfinal.txt
		
		echo " " >> logs/oracle/logfinal.txt
		echo " " >> logs/ecomex/logfinal.txt
		
		cat logs/oracle/oracle_automatic.log >> logs/oracle/logfinal.txt
		cat logs/ecomex/kecomex_automatic.log >> logs/ecomex/logfinal.txt