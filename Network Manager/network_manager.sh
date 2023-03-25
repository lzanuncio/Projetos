#!/bin/bash

# Rotina para Manutencao de Usuarios nos Servidores Linux, bem como alguns dos Servicos mantidos pelo SCOP
# Elaborado por Luis Fernando Zanuncio em 2018, editado por Diego L. Amador da Silva em 2018
# Assinatura autoral: Swamp booger

# Historico de revisoes
# Versao 1.0 - Juncao do NMANUTS e Libera
# Versaoes intermediarias - funcionalidades foram adicionadas
# Versao 2.0 - Faz todas as rotinas relacionadas aos usuarios no Servidor1 e Servidor2, no que se refere ao Samba, bat e CIA
# Versao 2.1 - Alterado apenas para realizar um loop e não fechar o sistema ao finalizar.

# As versoes intermediarias encontram-se na pasta net_old. 
# Algumas apresentam determinados bugs que foram corrigidos nas versoes posteriores. Cuidado ao utilizar
# Caso este script seja atualizado, semrpe realizar uma copia de seguranca e salva-lo na pasta net_old

#### palavras do autor ####
# Este script foi desenvolvido com permissao da chefia e do SRCOM, sendo que este ultimo mentoriou o processo
# Quaisquer duvidas sobre a funcionalidade do programa deve ser dirigida ao SRCOM, que conhece e e responsavel pelo processo
# Caso encontrem algum BUG ou mal funcionamento, reportar imediatamente ao SRCOM e a chefia do SCOP
# A ideia do Programa e que facilitasse as atividades que passaram a ser realizadas pelo SCOP, ate entao
# realizadas pelos analistas do SRCOM. O Script salva todas os acontecimentos em log para futuras consultas
# Eventuais funcionalidades que devam ser atribuidas devem ser informadas diretamente ao SRCOM
# Independente de quem for alterar, sempre o faca com atencao e carinho. Agradeco
# Use com moderacao

# variaveis de log para a migracao do usuario
log_migraUsuario=/root/shellpro/migraUsuario/migraUsuario.log
relatorio_migraUsuario=/root/shellpro/migraUsuario/relMigrados/Relatorios_Usuarios_Romisa_Migrados.txt

# Declaracao da Linguagem da Sessao
LANG=en
export LANG

#outras declaracoes

COUNT4=0
COUNT3=0
COUNT2=0
COUNT=0
OPC=0
i=0
veremail=0
SRC=0
CONTVER=0
rest_Servidor1=0
rest_Servidor2=0
narq_Servidor1=""
narq_Servidor2=""
mapeamentos_comentados=0

data=`date +%d-%m-%y`

LOG=/root/shellpro/operacao/logs/network_manager_$$_$data.log

#########################################################################
################### SECAO DE PROCEDURES DO SCRIPT  ######################

# 0 - Verifica se o usuario e aprovador de algum recurso, usado pela pesquisa e exclusao de mapeamentos e usuario
TesteUsuarioAprovador(){
	
	# verifica se o usuario e aprovador de algum recurso no Servidor1
	aprovaddor_Servidor1=$(cat /etc/samba/smb.conf |grep $user |wc -l)
	if [ $aprovaddor_Servidor1 == 0 ]; then
		aprovador=0
	else
		aprovador=1
	fi
	
	# verifica se o usuario e aprovador de algum recurso no Servidor2
	aprovaddor_Servidor2=$(ssh Servidor2 cat /etc/samba/smb.conf |grep $user |wc -l)
	if [ $aprovaddor_Servidor2 == 0 ]; then
		aprovador=0
	else
		aprovador=1
	fi
	
	# retorna o valor encontrado
	return "$aprovador"
}

# 0 - Procedure que verifica se o usuario existe, abortando o script caso nao exista
TestaUsuarioExisteServidor1(){

	#testa se algum login foi digitado, se a variavel user nao esta vazia
	if [ -z $user ]; then
		echo "Nenhum login foi digitado. Favor digitar algum login - 504" |tee -a $LOG
		exit
	else
		#conta a quantidade de linhas encontradas que possuem o login informado
		COUNT=`cat /etc/passwd |grep -i /$user: |wc -l`

		#caso mais que linha (login) seja encontrada, ira confirmar qual dos logins deseja verificar
		if [ $COUNT -gt 1 ]
		then
			cat /etc/passwd |grep -i /$user: | awk -F: ' $1 ~ /'$user'/  { print $1 ", " $5 }'
			echo -e " "
			echo -e " - Mais de um usuario foi encontrado no Servidor1, favor confirmar o login do usuario que deseja localizar - 502" |tee -a $LOG
			echo -e " "
			read user
			#testa se algum login foi digitado, se a variavel user nao esta vazia
			if [ -z $user ]; then
				echo "Nenhum login foi digitado. Favor digitar algum login - 504" |tee -a $LOG
				exit
			fi

		else
			#nao tendo localizado varios usuarios, vai ver se nenhum foi encontrado
			cat /etc/passwd |grep /$user: >/dev/null
			if [ $? != 0 ]; then
				echo -e " - $user NAO existe no Servidor1 - 506" |tee -a $LOG
				exit
			else
				#pega o nome do usuario
				name_Servidor1=`cat /etc/passwd | grep /$user: |cut -d":" -f5`

				#testa se encontrou algum nome de usuario, depois de ter encontrado algum usuario para verificar seu cadastro
				if [ -z "$name_Servidor1" ]; then
					echo "Nenhum nome de usuario foi localizado, favor confirmar o registro - 500" |tee -a $LOG
					exit
				fi
			fi
		fi
	fi
}

# 0 - Procedure que verifica se o usuario existe no Servidor2
TestaUsuarioExisteServidor2(){
		#conta a quantidade de linhas encontradas que possuem o login informado
		COUNT=`ssh Servidor2 cat /etc/passwd |grep -i /$user: |wc -l`

		#caso mais que linha (login) seja encontrada, ira confirmar qual dos logins deseja verificar
		if [ $COUNT -gt 1 ]
		then
			ssh Servidor2 cat /etc/passwd |grep -i /$user: | awk -F: ' $1 ~ /'$user'/  { print $1 ", " $5 }'
			echo -e " "
			echo -e " - Mais de um usuario foi encontrado no Servidor2, favor confirmar o login do usuario que deseja localizar - 502" |tee -a $LOG
			echo -e " "
			read user
			#testa se algum login foi digitado, se a variavel user nao esta vazia
			if [ -z $user ]; then
				echo "Nenhum login foi digitado. Favor digitar algum login - 504"|tee -a $LOG
				exit
			fi
			echo $user >>$LOG
			echo " "

		else
			#nao tendo localizado varios usuarios, vai ver se nenhum foi encontrado
			ssh Servidor2 cat /etc/passwd |grep /$user: >/dev/null
			if [ $? != 0 ]; then
				echo -e " - $user NAO existe no Servidor2 - 506" |tee -a $LOG
				exit
			else
				#pega o nome do usuario
				name_Servidor2=`ssh Servidor2 cat /etc/passwd | grep /$user: |cut -d":" -f5`

				#testa se encontrou algum nome de usuario, depois de ter encontrado algum usuario para verificar seu cadastro
				if [ -z "$name_Servidor2" ]; then
					echo "Nenhum nome de usuario foi localizado, favor confirmar o registro - 500"|tee -a $LOG
					exit
				fi
			fi
		fi

}

# 0 - Procedure exibe quota do usuario, covertida em Mb
ExibeQuota (){
	repquota -as | grep $user | tr -s ' ' ' ' | cut -f5 -d" "
}

# 0 - Verifica letras disponiveis na bat do usuario
VerificaLetrasDisponiveis (){

		#pega as letras dos recursos utilizados atualmente
		if [ $mapeamentos_comentados == 0 ]; then
			#usado para liberacao de recursos. Desconsidera mapeamentos comentados
			mapeamentos=(${mapeamentos[@]} `sed '/user:/!d;/^rem\|REM\|Rem/d' /root/shellpro/migraUsuario/BatsMigradas/$user.bat | cut -d" " -f3 | tr -d :`)
		else
			if [ $mapeamentos_comentados == 1 ]; then
				#usado para arrumar a bat. Considera mapeamentos comentados e usa a bat a partir da area temporaria
				mapeamentos=$(sed '/user:/!d;/rem/ s/rem //g' /root/shellpro/migraUsuario/BatsMigradas/$user.bat | cut -d" " -f3 | tr -d :)
			
			else
				#usado para verificar as letras disponiveis na bat provisoria
				mapeamentos=$(sed '/user:/!d;/rem/ s/rem //g' /home/netlogon/$user.bat | cut -d" " -f3 | tr -d :)
			fi
		fi
		#definicao das letras que podem ser mapeadas
		opcoes_letras=("f" "g" "h" "i" "j" "k" "l" "m" "n" "o" "p" "q" "r" "s" "t" "u" "v" "w" "x" "y" "z")

		#ira verificar quais letras estao disponiveis
		x=0	
		z=0
		for x in `echo ${!opcoes_letras[*]}`;do
			usada=0
			y=0
			for y in `echo ${!mapeamentos[*]}`;do			
				if [ "${opcoes_letras[$x]}" == "${mapeamentos[$y]}" ]; then
					usada=1					
				fi
			done			
			if [ "$usada" != 1 ]; then
				letras_livres[$z]=${opcoes_letras[$x]}
				let "z = z + 1"
			fi
		done
}

# 1 - Procedure de EXCLUSAO de Mapeamento
ExclusaoMap(){

	# verifica se o usuario e aprovador de recurso
	TesteUsuarioAprovador
	if [ "$aprovador" != 0 ]; then
		echo " "
		echo "Usuario $user e aprovador de recursos. Comunicar o SRCOM via e-mail questionando o novo aprovador" |tee -a $LOG
		PesquisaAprovador
	
		echo " "
		echo "Tem certeza que deseja prosseguir com a revogacao dos mapeamentos desse usuario APROVADOR? S/N"|tee -a $LOG
		echo " "
		
		read resp_desl
		echo $resp_desl >> $LOG
		
		
		
		if [ "$resp_desl" == "S" ] || [ "$resp_desl" == "s" ];then
		
			#vai enviar o e-mail
			RECIPIENT=srcom@romi.com
			SENDER=scop@romi.com
			CCO=scop@romi.com
			USUARIO=$user
			
			/usr/sbin/sendmail -i -v -Am -f $SENDER $RECIPIENT $CCO $DESLIGADO > /dev/null<<END
to: $RECIPIENT
from: $SENDER
bcc: $CCO
subject: Usuario aprovador de recursos de rede

Prezados(as),

Informamos que devido a trasnferencia ou desligamento de $USUARIO, Ã© necessÃ¡rio rever quem serÃ¡ o aprovador dos recursos abaixo:

$(PesquisaAprovador)

Att.,

Setor da Central de OperaÃ§Ãµes
IndÃºstrias Romi S.A.
+55 (19)Â 3455 9960 |Â +55 (19)Â 3455 9961
scop@romi.com

END
			echo "enviado e-mail para o SRCOM com os recursos que o usuario e aprovador" |tee -a $LOG
		fi
	fi
	
	if [ "$resp_desl" == "S" ] || [ "$resp_desl" == "s" ] || [ "$aprovador" == 0 ] ;then

		echo "Levantando os acessos do usuario" |tee -a $LOG
		echo " "
			
		#ira exibir as informacoes do usuario de mapeamentos
		InformacaoUsuarioMapeamentos		

		#caso o usuario nao tenha recurso nem no Servidor2 nem no Servidor1:
		if [ "$var_tmp1_rec_Servidor2" == "  " ] && [ "$var_tmp2_rec_Servidor1" == "  " ]; then
			echo " "
			echo "Nenhum recurso a ser removido, tanto no Servidor1 quanto no Servidor2" |tee -a $LOG
		#se tem algum recurso, vai perguntar se pode remover
		else
			echo " "
			echo " Deseja excluir os mapeamentos de $name?"|tee -a $LOG
			echo " "
			echo "1 - Servidor2"
			echo "2 - Servidor1"
			echo "3 - Ambos"
			echo "4 - Cancelar"

			echo " "
			read OPC_ExcludeMap
			echo $OPC_ExcludeMap >>$LOG

			if [ $OPC_ExcludeMap != 1 ] && [ $OPC_ExcludeMap != 2 ] && [ $OPC_ExcludeMap != 3 ]; then
				echo "Nenhum acesso foi removido"|tee -a $LOG
			else
				deleta_usuario="sim"
				#daqui para frente remove os acessos no Servidor2 e Servidor1
				#Servidor2
				if [ $OPC_ExcludeMap == 1 ] || [ $OPC_ExcludeMap == 3 ]; then	
					if [ -z $recursos_Servidor2 ]; then
						echo ""
						echo "Nenhum recurso no Servidor2" |tee -a $LOG
					else
						for x in `echo ${!recursos_Servidor2[*]}`;do
							echo "Excluido o mapeamento ${recursos_Servidor2[$x]} do login $user no Servidor2" |tee -a $LOG
							ssh Servidor2 "gpasswd -d $user ${recursos_Servidor2[$x]} >> /dev/null &
							echo '${recursos_Servidor2[$x]}' >> /root/shellpro/revaccess/log/'$user'_'$data'.log"
						done
					fi
				fi

					echo " "

					#Servidor1
					if [ $OPC_ExcludeMap == 2 ] || [ $OPC_ExcludeMap == 3 ]; then
						if [ -z $recursos_Servidor1 ]; then
							echo ""
							echo "Nenhum recurso no Servidor1" |tee -a $LOG
						else
							for x in `echo ${!recursos_Servidor1[*]}`;do
								echo "Excluido o mapeamento ${recursos_Servidor1[$x]} do login $user no Servidor1" |tee -a $LOG
								gpasswd -d $user ${recursos_Servidor1[$x]} >> /dev/null &
								echo ${recursos_Servidor1[$x]} >> /root/shellpro/revaccess/log/$user"_"$data".log"
							done
						fi
					fi
					
					#atualiza a bat
					ReorganizaBat
				fi
			fi
		
	fi

}

# 2 - Procedure de Entrada de InformacÃµes sobre o Usuario - resumida
InformacaoUsuarioResumida(){

	echo " "
	
	#pega codigo do grupo do usuario (grupo do setor)
	Cods=`cat /etc/passwd |grep /$user: |cut -d":" -f4`

	#usa o codigo obtido acima para pegar a sigla do setor 	
	SiglaSetor=`cat /etc/group |grep :$Cods: |cut -d":" -f1`
	echo -e " $user - $name"|tee -a $LOG
	echo -e " "
	echo -e " Existe no Servidor1, setor $SiglaSetor" |tee -a $LOG
	echo -e " "
	echo " A quota desse usuario e: "$(ExibeQuota) | tee -a $LOG
	
}

# 3 - Procedure para criar usuario no Servidor1
CriacaoUsuarioServidor1(){

	echo -e " Informe o login do novo usuario: " |tee -a $LOG
	read user

	# deixa o usuario todo em minusculo e retira espacos e caracteres especiais, menos . e - _
	user=$(echo "$user"| tr 'A-Z' 'a-z' | sed 's/[\ *$?#@!%&,"]//g')
	echo $user >>$LOG

	#testa se algum login foi digitado, se a variavel user nao esta vazia
	if [ -z $user ]; then
		echo "Nenhum login foi digitado. Favor digitar algum login - 504" |tee -a $LOG
		exit
	else		
		#verifica se o usuario existe. Caso exista, aborta o script. Caso contrario, segue para criacao
		cat /etc/passwd |grep /$user: >/dev/null
		if [ $? == 0 ]; then
			echo " Usuario ja existe no Servidor1. Segue as informacoes resumidas:" |tee -a $LOG
			InformacaoUsuarioResumida
			exit
		else
			#solicita as informacoes para criacao
			echo " Usuario nao existe no Servidor1. Script ira prosseguir para a criacao" |tee -a $LOG
			echo -e " Informacces para criacao em servidores..." |tee -a $LOG
			echo " "
			echo -e " Entre com o nome COMPLETO do usuario" |tee -a $LOG
			read name

			echo -e " "
			echo -e " Entre com a sigla do setor" |tee -a $LOG
			read SiglaSetor
			# deixa o setor todo em minusculo, alem de retirar - _ e espacos
			SiglaSetor=$(echo "$SiglaSetor"| tr 'A-Z' 'a-z' | sed 's/[\_ -]//g')

			echo -e " "
			#gera a senha automaticamente, usando o PID, username e hora atual, aplicando uma hash ao fim
			SenhaLogin=$(echo -n $$$user`date +%H%M%S` | md5sum | sed 's/[\ -]//g')
			SenhaLogin=${SenhaLogin:10:8}
			echo " Inserindo a senha: "$SenhaLogin
			echo " Senha randomica inserida automaticamente" >> $LOG

			#testa se as informacoes necessarias foram preenchidas
			if [ -z $SiglaSetor ] || [ -z "$name" ] || [ -z $SenhaLogin ]; then
				echo -e "\e[m - Campos de preenchimento OBRIGATORIO!  " |tee -a $LOG
				exit
			else
				#comandos para criacao do usuario
				groupadd $SiglaSetor
				useradd -g $SiglaSetor -c "$name" $user
				edquota -p padrao1 $user

				#chama programa expect, que simula o usuario na hora de inserir a senha

/usr/bin/expect <<- END

	set timeout -1

	spawn smbpasswd -a $user

	expect "SMB password:"
	send -- "$SenhaLogin\r"

	expect "SMB password:"
	send -- "$SenhaLogin\r"

	send -- "\r"

	expect eof
END

				#valida se a senha aplicada esta igual a senha informada
				senha1_tmp=$(pdbedit -w $user | cut -f 4 -d:)
				senha2_tmp=$(echo -n "$SenhaLogin" | iconv -t utf16le | openssl md4 | tr 'a-z' 'A-Z' | cut -f2 -d" ")
				if [ -z "$senha1_tmp" ] || [ "$senha1_tmp" != "$senha2_tmp" ]; then
					echo "Ocorreu um problema com a senha no samba ou expect. 103" |tee -a $LOG
					exit
				else
					echo "Usuario criado com sucesso no Servidor1" |tee -a $LOG

					#criacao da bat. Insere o mapeamento e o cabecalho de toda a bat
					echo "title Mapeando Recursos de Rede, Aguarde!" > /home/netlogon/$user.bat
					echo "@echo off" >> /home/netlogon/$user.bat
					echo " " >> /home/netlogon/$user.bat
					echo "net use * /delete /y" >> /home/netlogon/$user.bat
					echo " " >> /home/netlogon/$user.bat
					echo "net use b: \\\Servidor1\\$user $SenhaLogin /user:$user /persistent:no" >> /home/netlogon/$user.bat
					echo " " >> /home/netlogon/$user.bat
					echo "rem \\\Servidor1\public\deploy\deploy.bat" >> /home/netlogon/$user.bat
					echo "rem \\\Servidor1\public\deploy\backinfo\backInfo.exe" >> /home/netlogon/$user.bat
					echo " " >> /home/netlogon/$user.bat

					#mudanca da permissao da bat
					chmod 0700 /home/netlogon/$user.bat

					#muda o proprietario do arquivo, colocando o proprio usuario
					chown $user:$SiglaSetor /home/netlogon/$user.bat

					#migra a bat do usuario
					if [ `mount | grep -e "192.168.4.101" -e BatsMigradas | wc -l` = 2 ]; then
						if [ `ls -l /home/netlogon/$user.bat | wc -l` = 1 ]; then

							#tratam a bat em seu local temporario
							dthora=`date +%d-%m-%y'('%H:%M:%S')'`
							#cp /home/netlogon/$user.bat /root/shellpro/migraUsuario/bkpBAT
							echo "rem usuario migrado para ambiente sso em $dthora " >> /home/netlogon/$user.bat

							# move a bat atual para a pasta de bats migradas, onde eh lido pelo DC e ADC
							mv /home/netlogon/"$user".bat /root/shellpro/migraUsuario/BatsMigradas

							#conversao para leitura do ambiente windows
							unix2dos /root/shellpro/migraUsuario/BatsMigradas/"$user".bat 

							# adiciona informacoes de data e hora nos logs
							echo "Usuario: $user Migrado em $dthora " >> $log_migraUsuario | tee -a $relatorio_migraUsuario

							# contagem de usuarios migrados
							totalMigradosAntigos=`cat /root/shellpro/migraUsuario/relMigrados/Relatorios_Usuarios_Romisa_Migrados.txt | grep "TOTAL de Usuarios Migrados:" | awk -F": " '{print $2}'`
							totalMigradosAgora=`cat /root/shellpro/migraUsuario/relMigrados/Relatorios_Usuarios_Romisa_Migrados.txt | grep Usuario: | wc -l`

							#atualiza a informacao de total de usuarios migrados no relatorio
							sed -i 's/Migrados: '$totalMigradosAntigos'/Migrados: '$totalMigradosAgora'/gi' $relatorio_migraUsuario

							echo "Bat criada e migrada com sucesso" |tee -a $LOG

						else
							# a bat nao foi encontrada pelo comando ls. Deve ter ocorrido um erro na sua criacao
							echo "015 - Usuario $user ja foi migrado ou a BAT dele nao existe!" |tee -a $log_migraUsuario
							exit
						fi
					else
						echo "020 - Nao foi possivel fazer a migracao, verifique se os volumes 192.168.4.101:/dados/default/servicos/area_compartilhada e //192.168.4.47/netlogon estao montados!" |tee -a $LOG
						exit
					fi

					dthora2=`date +%d-%m-%y'('%H:%M:%S')'`
					echo "Operacao Finalizada em $dthora2" >> $log_migraUsuario

					#criacao de usuario no Servidor2
					CriacaoUsuarioServidor2
				fi
			fi
		fi
	fi
}

# 4 - Procedure para criar usuario no Servidor2 
CriacaoUsuarioServidor2(){

	#verifica se o usuario ja existe no Servidor2
	comp_sig=`ssh Servidor2 cat /etc/passwd | grep -w $user: | cut -d: -f1`
	if [ $user == "$comp_sig" ]; then
		echo "Usuario ja existe no Servidor2" |tee -a $LOG
		exit
	else

		#pega codigo do grupo do usuario (grupo do setor)
		Cods=`cat /etc/passwd |grep /$user: |cut -d":" -f4`

		#usa o codigo obtido acima para pegar a sigla do setor
		SiglaSetor=`cat /etc/group |grep :$Cods: |cut -d":" -f1`

		#pega a senha atual do usuario pela bat		
		cp /root/shellpro/migraUsuario/BatsMigradas/$user".bat" /home/netlogon
		dos2unix /home/netlogon/$user".bat"
		SenhaLogin=$(sed '/Servidor1\\'$user'/!d' /home/netlogon/$user".bat" | cut -f5 -d" ")
		
		# tira possiveis espacos
		SenhaLogin=$(echo "$SenhaLogin" | sed 's/ //g')

		#testa se as informacoes necessarias foram preenchidas
		if [ -z $SiglaSetor ] || [ -z "$name" ] || [ -z $SenhaLogin ]; then
			echo -e "\e[m - Campos de preenchimento OBRIGATORIO!  " |tee -a $LOG
			exit
		else
			#usuario sera adicionado no Servidor2, com o grupo criado e quota informada
			ssh Servidor2 "groupadd $SiglaSetor ; useradd -s /bin/false -g $SiglaSetor -c '$name' $user ; edquota -p padrao $user"

			#expect ira preencher as senhas no servidor Servidor2
/usr/bin/expect <<- END

	set timeout -1

	spawn ssh root@Servidor2

	expect "#"
	send -- "passwd $user\r"

	expect "password:"
	send -- "$SenhaLogin\r"

	expect "password:"
	send -- "$SenhaLogin\r"

	expect "#"
	send -- "smbpasswd -a $user\r"

	expect "SMB password:"
	send -- "$SenhaLogin\r"

	expect "SMB password:"
	send -- "$SenhaLogin\r"

	send -- "\r"

	expect "#"
	send -- "exit\r"

	expect eof
END

			sleep 1

			#valida se a senha aplicada esta igual a senha informada
			senha3_tmp=$(ssh Servidor2 "pdbedit -w $user | cut -f 4 -d:")
			senha4_tmp=$(ssh Servidor2 "echo -n $SenhaLogin | iconv -t utf16le | openssl md4 | tr 'a-z' 'A-Z' | cut -f2 -d' '")

			if [ -z $senha3_tmp ] || [ $senha3_tmp != $senha4_tmp ]; then
				echo "Ocorreu um problema com a senha no samba ou expect. 103" |tee -a $LOG
				exit
			else
				echo "Usuario criado com sucesso no Servidor2" |tee -a $LOG
			fi
		fi
	fi
}

# 5 - Exclusao de Usuario
ExclusaoUsuario(){

	#chama a func 1 para excluir os mapeamentos antes de prosseguir
	ExclusaoMap

	if [ "$OPC_ExcludeMap" == "3" ] || [ -z $OPC_ExcludeMap ]; then

		echo "Os acessos desse usuario foram registrados em /var/log/removidos.log" |tee -a $LOG

		#apaga a bat do usuario do local provisorio
		rm -f /home/netlogon/$user.bat

		echo " "

		# busca a informacao de arquivos na area de backup pessoal do usuario
		echo "----------------------------------------------------" 
		echo " Conteudo da area de backup passoal de $user" |tee -a $LOG
		echo " "

		total=`ls -ltrh /home/$user | grep total | cut -f2 -d" "`

		if [ "$total" != "0" ]; then 
			echo "Tem arquivos: "$total | tee -a $LOG
			ls -lthr /home/$user | grep -v "total" |tee -a $LOG
		else
			echo "Nenhum arquivo: "$total | tee -a $LOG
		fi

		#exibe o total de espaco utilizado pelos arquivos encontrados
		du -sh /home/$user 

		echo " "
		echo "----------------------------------------------------"

		echo -e " - Deseja mover os arquivos do usuario e enviar e-mail para a chefia? S ou N" |tee -a $LOG
		read respma
		echo $respma >>$LOG
			
		if [ $respma = s -o $respma = S ]; then

			echo -e " - Entre com o login(somente) do destinatario (chefia) " |tee -a $LOG
			read emaild
			echo $emaild >>$LOG

			#transfere os arquivos para o chefe
			mkdir -p /home/$emaild/$user
			cp -Rpf /home/$user/* /home/$emaild/$user
			chmod -R 0777 /home/$emaild/$user

			#vai enviar o e-mail
			RECIPIENT=$emaild@romi.com
			SENDER=scop@romi.com
			CCO=scop@romi.com
			DESLIGADO=$user

			/usr/sbin/sendmail -i -v -Am -f $SENDER $RECIPIENT $CCO $DESLIGADO<<END
to: $RECIPIENT
from: $SENDER
bcc: $CCO
subject: Arquivos de backup de colaborador desligado

Prezado(a),

Informamos que devido ao desligamento do(a) funcionario(a) $DESLIGADO, os arquivos existentes na area de backup desse(a) funcionario(a) foram transferidos para sua area de backup. Os arquivos podem ser observados atraves do Windows Explorer, na unidade (B:)

Att.,

Setor da Central de OperaÃ§Ãµes
IndÃºstrias Romi S.A.
+55 (19)Â 3455 9960 |Â +55 (19)Â 3455 9961
scop@romi.com

END
			echo "Enviado e-mail sobre o desligamento" |tee -a $LOG
		fi
			
		echo -e " - Deseja Deletar o usuario? S ou N" |tee -a $LOG
		read respm
		echo $respm >>$LOG
			
		if [ $respm = s -o $respm = S ]; then
				
			#excluindo o usuario de fato
			smbpasswd -x $user
			userdel -r $user

			ssh Servidor2 "smbpasswd -x $user
			userdel -r $user"
			
			echo "Removido em ";date;id $user >>/var/log/removidos.log | tee -a $LOG
			echo "Desligamento concluido com sucesso" |tee -a $LOG

		else
			echo "Usuario nao foi deletado." |tee -a $LOG
		fi
	else
		echo "Necessario excluir os mapeamentos previamente a exclusao do usuario"  |tee -a $LOG
	fi
}

# 6 - Pesquisa de usuario aprovador de recurso
PesquisaAprovador (){
	# verifica se o usuario e aprovador em algum lugar
	TesteUsuarioAprovador
	if [ "$aprovador" == 0 ]; then
		echo " "
		echo "Usuario nao e aprovador de recursos nem no Servidor2 nem no Servidor1" |tee -a $LOG
	else
		#verifica se possui mais de 0 grupos em que e aprovador
		COUNT=`cat /etc/samba/smb.conf |grep -w $user |wc -l`
		if [ $COUNT == 0 ]; then
			echo " "
			echo "Usuario $user nao e aprovador de nenhum recurso no Servidor1" |tee -a $LOG
		else
			echo " "
			echo "Usuario $user e aprovador no Servidor1 de:" |tee -a $LOG
			echo " "
			cat /etc/samba/smb.conf | grep -B1 $user | sed '/^$\|'$user'\|--\|#/d'
		fi
		
		#verifica se possui mais de 0 grupos em que e aprovador
		COUNT2=`ssh Servidor2 cat /etc/samba/smb.conf |grep -w $user |wc -l`
		if [ $COUNT2 == 0 ]; then
			echo " "
			echo "Usuario $user nao e aprovador de nenhum recurso no Servidor2" |tee -a $LOG
		else
			echo " "
			echo "Usuario $user e aprovador no Servidor2 de:" |tee -a $LOG
			echo " "
			ssh Servidor2 "cat /etc/samba/smb.conf | grep -B1 $user | sed '/^$\|'$user'\|--/d'"
			echo " "
		fi
	fi
}

# 7 - Procedure para liberacao dos recursos nos servidores
LiberacaoRecursos(){


	##
	caracter_especial=`cat /home/netlogon/$user".bat" | grep '$'`
	


	#verifica se o usuario esta cadastrado no Servidor2
	TestaUsuarioExisteServidor2

	#verifica quais letras podem ser utilizadas para a liberacao
	VerificaLetrasDisponiveis
		
	if [ ${#letras_livres[@]} == 0 ]; then

		echo "Limite de letras utilizadas foi alcancado. Favor analisar a bat antes de proceder com a liberacao" |tee -a $LOG
		echo "Deseja validar os mapeamentos e a BAT? S/N"
		read resp_bat

		if [ "$resp_bat" == "S" -o "$resp_bat" == "S" -o ]; then
				ReorganizaBat
		fi

	else
			
		echo "Voce esta liberando acesso para $name" |tee -a $LOG
		echo " "
			
		echo "letras disponiveis"
		for x in `echo ${!letras_livres[*]}`;do
			echo ${letras_livres[$x]}":"
		done

		echo "Informe a letra que deseja liberar" |tee -a $LOG
		read letra
		echo $letra >>$LOG

		# deixa a letra todo em minusculo
		letra=$(echo "$letra"| tr 'A-Z' 'a-z')
			
		#verifica se a letra informada Ã© uma das utilizadas, liberado a primeira disponivel caso seja
		a=0
		disponivel=0
		for a in `echo ${!letras_livres[*]}`;do			
			if [ "$letra" == "${letras_livres[$a]}" ]; then
					disponivel=1
			fi
		done
					
		if [ "$disponivel" != 1 ]; then
			echo "Letra escolhida ja esta sendo utilizada. Sera liberado na letra " ${letras_livres[0]}":"|tee -a $LOG
			letra=${letras_livres[0]}
		fi
			
		#informacao do servidor em que sera liberado o recurso
		echo " "
		echo " Escolha o servidor"
		echo " "
		echo " 1 - Servidor2"
		echo " 2 - Servidor1"
		echo " 3 - Cancelar"		
		read servidor

		#pega o nome do recurso
		echo "Informe o recurso a ser liberado"|tee -a $LOG			
		read smb
		smb=$(echo "$smb" | tr 'A-Z' 'a-z' | sed 's/ //g')
				
		echo $servidor" "$smb >> $LOG
			
		case $servidor in
			1|s|S)server="Servidor2"
				compsamb=`ssh Servidor2 cat /etc/group | sed 's/^\|$\|:/,/g' | grep ','$smb'_rx,' | rev | cut -f2-5 -d'_' | rev | sed 's/,//g'`
				if [ "$smb" == "$compsamb" ]; then
					localizou=1
				else
					localizou=0
				fi						
			;;

			2|l|L)server="Servidor1"
				compsamb=`cat /etc/group | sed 's/^\|$\|:/,/g' | grep ','$smb'_rx,' | rev | cut -f2-5 -d'_' | rev | sed 's/,//g'`
				if [ "$smb" == "$compsamb" ]; then
					localizou=1
				else
					localizou=0
				fi						
			;;

			*)  echo -e "Operacao Cancelada!" |tee -a $LOG
				echo " "
				exit
			;;
		esac

		#verifica se o recurso existe no servidor
		if [ "$localizou" == 0 ]; then
			echo "Recurso "$smb" nao foi localizado." |tee -a $LOG
		else

			#ira perguntar o tipo de liberacao desejada
			echo "Informar o tipo de acesso:" |tee -a $LOG
			echo " "
			echo "1 - Leitura RX"
			echo "2 - Gravacao RW"
			echo " "
			read opcao
			echo $opcao >> $LOG

			case $opcao in
				1|rx|RX|rX|Rx)liberacao=rx
				liberacao_inversa=rw
				;;

				2|rw|RW|rW|rW)liberacao=rw
				liberacao_inversa=rx
				;;

				*)echo -e "Opcao Invalida de acesso!" |tee -a $LOG
				echo " "
				exit
				;;

			esac

			#verifica se o usuario ja possui o recurso liberado, removendo o de permissao diferente quando aplicado e adicionando o servidor
			if [ $server == "Servidor2" ]; then
				COUNT3=`ssh Servidor2 cat /etc/group | sed 's/^\|$\|:/,/g' | grep ,$user, | grep $smb'_'$liberacao | wc -l`			
				if [ $COUNT3 == 1 ]; then
					echo "Recurso ja esta liberado"|tee -a $LOG
					ReorganizaBat
				else
					COUNT4=`ssh Servidor2 cat /etc/group | sed 's/^\|$\|:/,/g' | grep ,$user, | grep $smb'_'$liberacao_inversa | wc -l`
					if [ $COUNT4 == 1 ]; then
						echo "Removendo atual acesso com permissao difetente"|tee -a $LOG
						ssh Servidor2 gpasswd -d $user $smb"_"$liberacao_inversa
					fi
					ssh Servidor2 gpasswd -a $user $smb"_"$liberacao
					echo "Recurso foi Liberado"|tee -a $LOG
				fi
			else
				COUNT3=`cat /etc/group | sed 's/^\|$\|:/,/g' | grep ,$user, | grep $smb'_'$liberacao | wc -l`			
				if [ $COUNT3 == 1 ]; then
					echo "Recurso ja esta liberado"|tee -a $LOG
					ReorganizaBat
				else
					COUNT4=`cat /etc/group | sed 's/^\|$\|:/,/g' | grep ,$user, | grep $smb'_'$liberacao_inversa | wc -l`
					if [ $COUNT4 == 1 ]; then
						echo "Removendo atual acesso com permissao difetente"|tee -a $LOG
						gpasswd -d $user $smb"_"$liberacao_inversa
					fi
					gpasswd -a $user $smb"_"$liberacao
					echo "Recurso foi Liberado"|tee -a $LOG
				fi
			fi

			if [ $COUNT3 != 1 ]; then
				#copia a bat para uma area de uso temporario
				cp /root/shellpro/migraUsuario/BatsMigradas/$user".bat" /home/netlogon

				#converte a bat para uso do linux
				dos2unix /home/netlogon/$user".bat"

				#pega a senha da bat, insere a linha do novo recurso liberado e ordena por ordem alfabetica
				SenhaLogin=$(sed '/Servidor1\\'$user'/!d' /home/netlogon/$user".bat" | cut -f5 -d" ")
				echo 'net use '$letra': \\'$server'\'$smb' '$SenhaLogin' /user:'$user' /persistent:no' >> /home/netlogon/$user".bat"
					
				unix2dos /home/netlogon/$user".bat"
				cp /home/netlogon/$user".bat" /root/shellpro/migraUsuario/BatsMigradas/
				rm -f /home/netlogon/$user".bat"

				ReorganizaBat
			fi

			#envio do e-mail para o usuario
			echo "Informe o login de quem vai receber o e-mail. Insira 1 para mandar para o usuario que foi liberado" |tee -a $LOG
			read destinatario
			echo $destinatario >> $LOG
			if [ $destinatario == 1 ]; then
				destinatario=$user
			fi
			RECIPIENT=$destinatario@romi.com
			SENDER=scop@romi.com
			CCO=scop@romi.com

			/usr/sbin/sendmail -i -v -Am -f $SENDER $RECIPIENT $CCO<<END
to: $RECIPIENT
from: $SENDER
bcc: $CCO
subject: Chamado - Liberacao de Recurso de Rede

Prezado(a),

Informamos que foi concluÃ­da a liberaÃ§Ã£o de acesso ao recurso de rede $smb para "$name", conforme sua solicitaÃ§Ã£o atravÃ©s do sistema Litiris. 

NÃ£o serÃ¡ necessÃ¡ria nenhuma aÃ§Ã£o adicional, pois o recurso de rede serÃ¡ automaticamente mapeado nas prÃ³ximas vezes que o logon no Windows for executado.

Lembramos que o acesso Ã© individual e intransferÃ­vel, devendo ser utilizado somente no computador do usuÃ¡rio destinatÃ¡rio deste e-mail.

Em caso de dÃºvida ou dificuldade contate o Setor da Central de OperaÃ§Ãµes.

Att.,

Setor da Central de OperaÃ§Ãµes
IndÃºstrias Romi S.A.
+55 (19)Â 3455 9960 |Â +55 (19)Â 3455 9961
scop@romi.com

END
		fi
	fi
}

# 8 - Procedure para restauracao de acesso a recursos de rede
RestAcesso(){

	# pega qual o arquivo de log de revisao de acesso mais recente
	narq_Servidor1=$(cd /root/shellpro/revaccess/log; ls -t $user'_'* | head -1)
	narq_Servidor2=$(ssh Servidor2 "cd /root/shellpro/revaccess/log; ls -t $user'_'* | head -1")
	
	if [ -z "$narq_Servidor1" ] && [ -z "$narq_Servidor2" ]; then
		echo "Nao ha registros de acesso revogados no Servidor1 e Servidor2 para este usuario. Finalizando procedure" | tee -a $LOG
		exit
	else
		if [ -n "$narq_Servidor1" ] && [ -n "$narq_Servidor2" ]; then
			if [ "$narq_Servidor1" != "$narq_Servidor2" ]; then
			
				# obtem as datas dos arquivos mais recentes de revisao (acessos perdidos) do usuario
				date_Servidor1=$(date -r /root/shellpro/revaccess/log/$narq_Servidor1 +%Y%m%d)
				date_Servidor2=$(ssh Servidor2 "date -r /root/shellpro/revaccess/log/$narq_Servidor2 +%Y%m%d")

				if [  "$date_Servidor1" -gt "$date_Servidor2" ];then
					echo "Data da revisao no Servidor1 e mais recente do que no Servidor2" | tee -a $LOG
					narq=$narq_Servidor1
					rest_Servidor1=1	
				else
					echo "Data da revisao no Servidor2 e mais recente do que no Servidor1" | tee -a $LOG
					narq=$narq_Servidor2
					rest_Servidor2=1
				fi
			else
				echo "Datas mais recentes de revisao de mapeamentos coincidem" | tee -a $LOG
				narq=$narq_Servidor1
				rest_Servidor1=1
				rest_Servidor2=1
			fi
		else
			if [ -z "$narq_Servidor1" ]; then
				echo " Nao ha revisoes de acesso no Servidor1. Seguindo com o arquivo no Servidor2" | tee -a $LOG
				narq=$narq_Servidor2
				rest_Servidor2=1
			else
				echo " Nao ha revisoes de acesso no Servidor2. Seguindo com o arquivo no Servidor1" | tee -a $LOG
				narq=$narq_Servidor1
				rest_Servidor1=1
			fi
		fi
		
	fi
	
	#pega os acessos removidos do Servidor1
	echo " "
	echo "Revisando os acessos de "$name" em "$narq | tee -a $LOG
	echo " "
	
	#Servidor1
	if [ $rest_Servidor1 == 1 ]; then
		acessos=(${acessos[@]} `cat /root/shellpro/revaccess/log/$narq`)
		
		echo "Deseja liberar os acessos no Servidor1 abaixo? S/N" | tee -a $LOG
		echo ""
		cat /root/shellpro/revaccess/log/$narq
		echo ""
		read resp
		if [ "$resp" == "S" -o "$resp" == "s" ]; then
			veremail=1
			x=0
			#libera os recursos no Servidor1
			while [ $x != ${#acessos[@]} ]
			do
				echo "Removendo o acesso " ${acessos[$x]} >> $LOG
				gpasswd -a $user ${acessos[$x]}
				let x=x+1
			done
		fi
	fi
	
	if [ $rest_Servidor2 == 1 ]; then
		#Servidor2
		acessos_Servidor2=(${acessos_Servidor2[@]} `ssh Servidor2 cat /root/shellpro/revaccess/log/$narq`)
		
		echo "Deseja liberar os acessos no Servidor2 abaixo? S/N"
		echo ""
		ssh Servidor2 cat /root/shellpro/revaccess/log/$narq
		echo ""
		read resp
		if [ "$resp" == "S" -o "$resp" == "s" ]; then
			veremail=1
			x=0
			#libera os recursos no Servidor2
			while [ $x != ${#acessos_Servidor2[@]} ]
			do
				echo "Removendo o acesso " ${acessos[$x]} >> $LOG
				ssh Servidor2 gpasswd -a $user ${acessos_Servidor2[$x]}
				let x=x+1
			done
		fi
	fi
	
	#envia o e-mail para o usuario
	if [ $veremail -eq 1 ];then
	
		echo "Informe o login de quem vai receber o e-mail" |tee -a $LOG
		read destinatario
		echo $destinatario >> $LOG
		echo " "
		
		RECIPIENT=$destinatario@romi.com
		SENDER=scop@romi.com
		CCO=scop@romi.com
		/usr/sbin/sendmail -i -v -Am -f$SENDER $RECIPIENT $CCO<<END
to: $RECIPIENT
from: $SENDER
bcc: $CCO
subject: Chamado - Restabelecimento de Recursos de Rede

Prezado(a),

O acesso aos Recursos de Rede do usuÃ¡rio "$name" foram restabelecidos, conforme solicitado em Litiris.

Para que as alteraÃ§Ãµes entrem em vigor, Ã© necessario fazer logoff ou reiniciar o computador.

Caso os recursos nÃ£o apareÃ§am automaticamente, favor entrar em contato com o Setor da Central de OperaÃ§Ãµes.

Atenciosamente,

Setor da Central de OperaÃ§Ãµes
IndÃºstrias Romi S.A.
+55 (19)Â 3455 9960 |Â +55 (19)Â 3455 9961
scop@romi.com

END
	fi
	
	ReorganizaBat
}

# 9 - Procedure usada para atualizar bat de determinado usuario
ReorganizaBat (){

	##
	caracter_especial=`cat /home/netlogon/$user".bat" | grep '$'`
	
	if [ -z $caracter_especial	]; then
		echo "Existe caracter especial na bat. Nao sera reogranizada" |tee -a $LOG
	else	
	
		##
		echo "Nao ha caracteres especiais. Iniciando tratamento da bat" |tee -a $LOG
		
		rm -rf /home/netlogon/$user".bat"
		rm -rf /home/netlogon/$user"_new.bat"

		#copia a bat para uma area de uso temporario
		cp /root/shellpro/migraUsuario/BatsMigradas/$user".bat" /home/netlogon

		#converte a bat para uso do linux
		dos2unix /home/netlogon/$user".bat"
		
		#obtem a informacao de quando a bat foi migrada
		migrado=`cat /home/netlogon/$user".bat" | grep -i "migrado para ambiente"`
		
		#pega a senha da bat
		SenhaLogin=$(sed '/Servidor1\\'$user'/!d' /home/netlogon/$user".bat" | cut -f5 -d" ")

		#Deixa a bat toda em minusculo
		sed -i 'y/ABCDEFGHIJKLMNOPQRSTUVWXYZÃÃÃÃÃÃÃÃÃÃÃÃ/abcdefghijklmnopqrstuvwxyzÃ Ã¡Ã¢Ã£Ã©ÃªÃ­Ã³Ã´ÃµÃºÃ§/' /home/netlogon/$user".bat"

		
		# deleta AMBOS os espacos em branco final e inicial de cada linha. Remove repetidos rem
		sed -i 's/ $//g;/^$/d;s/rem.*rem/rem/;s/^[ \t]*//;s/[ \t]*$//g' /home/netlogon/$user".bat"
		
		#pega outas coisas
		mapeamentos_outros=$(sed '/echo off/d;/ migrado /d;/title /d;/ \/del/d;/pause/d;/deploy/d;/\\Servidor2\\/d;/\\Servidor1\\/d' /home/netlogon/$user".bat")
		
		# cabeÃ§alho de toda bat
		echo "title Mapeando Recursos de Rede, Aguarde!" > /home/netlogon/$user"_new.bat"
		echo "@echo off" >> /home/netlogon/$user"_new.bat"
		echo " " >> /home/netlogon/$user"_new.bat"	
		echo "net use * /delete /y" >> /home/netlogon/$user"_new.bat"
		echo " " >> /home/netlogon/$user"_new.bat"

		######################### fim do cabecalho e inicio da parte que atualiza os mapeamentos liberados ############
		
		#pega os recursos do samba liberados para o usuario no Servidor2
		recursos_Servidor21=(${recursos_Servidor21[@]} `ssh Servidor2 "cat /etc/group | sed '/#/d;s/^\|$\|:/,/g;s/_rx\|_rw\|_admin//g' | grep ',$user,' | cut -d',' -f2"`)

		#pega os recursos do samba liberados para o usuario no Servidor1
		recursos_Servidor11=(${recursos_Servidor11[@]} `cat /etc/group | sed '/#/d;s/^\|$\|:/,/g;s/_rx\|_rw\|_admin//g' | grep ",$user," | cut -d',' -f2`)
			
		# Pega da bat os recursos atualmente mencionados
		mapeamentos_ativos_Servidor2=(${mapeamentos_ativos_Servidor2[@]} $(sed '/Servidor1\\'$user'/d;/user:/!d;/persistent:/!d;/Servidor2/!d;s/rem//g;s/^ //g;s/\\\\Servidor2\\//g' /home/netlogon/$user".bat" | cut -f4 -d" "))
		mapeamentos_ativos_Servidor1=(${mapeamentos_ativos_Servidor1[@]} $(sed '/Servidor1\\'$user'/d;/user:/!d;/persistent:/!d;/Servidor1/!d;s/rem//g;s/^ //g;s/\\\\Servidor1\\//g' /home/netlogon/$user".bat" | cut -f4 -d" "))
			
		#calcula quantos recursos de rede o usuario dispoe
		A=`echo ${#recursos_Servidor21[*]}`
		B=`echo ${#recursos_Servidor11[*]}`	
		let C="$A + $B"
		
		#calcula quantos mapeamentos atualmente o usuario utiliza
		D=`echo ${#mapeamentos_ativos_Servidor2[*]}`
		E=`echo ${#mapeamentos_ativos_Servidor1[*]}`	
		let F="$D + $E"
		
		if [ $C -gt 22 ] || [ $F -gt 22 ]; then
			echo "Usuarios possui mais recursos de rede do que suportado. A bat sera apenas reorganizada" |tee -a $LOG
			#mapeamentos serao organizados por ordem alfabetica
			mapeamentos_ativos_bat=`sed '/user:/!d;/persistent:/!d' /home/netlogon/$user".bat"`
			echo "$mapeamentos_ativos_bat" | sort >> /home/netlogon/$user"_new.bat"
			echo " " >> /home/netlogon/$user"_new.bat"

		else
			#ira verificar as letras disponiveis para mapear recursos
			mapeamentos_comentados=1
			VerificaLetrasDisponiveis
			
			echo "Sera verificado os acessos atuais do usuario e adequado ao liberado" |tee -a $LOG
		
			####         Servidor1       ####
			
			x=0
			y=0
			z=0
			
			#conferindo Servidor1
			for x in `echo ${!recursos_Servidor11[*]}`;do
				liberado=0
				for y in `echo ${!mapeamentos_ativos_Servidor1[*]}`;do
					if [ ${recursos_Servidor11[$x]} == ${mapeamentos_ativos_Servidor1[$y]} ]; then
						liberado=1
						#Tirando o comentario caso haja
						sed -i '/'${recursos_Servidor11[$x]}'/ s/rem //g' /home/netlogon/$user".bat"
						y=`echo ${!mapeamentos_ativos_Servidor1[*]}`
					fi
				done
				if [ $liberado != 1 ]; then
					#recurso nao consta na bat, liberar
					echo "Recurso "${recursos_Servidor11[$x]}" nao consta na bat, liberando"  |tee -a $LOG
					echo 'net use '${letras_livres[$z]}': \\Servidor1\'${recursos_Servidor11[$x]}' '$SenhaLogin' /user:'$user' /persistent:no' >> /home/netlogon/$user".bat"
					let "z = z + 1"
				fi
			done
			
			x=0
			y=0
			
			#agora confere se tem algo na bat que nao tem liberado no Servidor1
			for x in `echo ${!mapeamentos_ativos_Servidor1[*]}`;do
				liberado=0
				for y in `echo ${!recursos_Servidor11[*]}`;do
					if [ ${recursos_Servidor11[$y]} == ${mapeamentos_ativos_Servidor1[$x]} ]; then
						liberado=1
						y=`echo ${!recursos_Servidor11[*]}`
					fi
				done
				if [ $liberado != 1 ]; then
					#recurso nao esta liberado no servidor, removendo da bat
					echo "Recurso "${mapeamentos_ativos_Servidor1[$x]}" nao esta liberado no servidor, removendo da bat" |tee -a $LOG
					sed -i '/'${mapeamentos_ativos_Servidor1[$x]}'/d' /home/netlogon/$user".bat"
				fi
			done
			
			x=0
			y=0
			
			#remove mapeamentos repetidos do Servidor1, deixando apenas um
			
			x=0
			y=0
			i=0
		
			while [ $x -le ${#mapeamentos_ativos_Servidor1[*]} ]; do
				
				y=$x
				while [ $y -le ${#mapeamentos_ativos_Servidor1[*]} ]; do
					
					if [ "${mapeamentos_ativos_Servidor1[$x]}" == "${mapeamentos_ativos_Servidor1[$y]}" ] && [ $x != $y ]; then
						sed -i '/'${mapeamentos_ativos_Servidor1[$y]}'/{H;x;/^\n/d;g;}' /home/netlogon/$user".bat"
						echo "Mapeamento "${mapeamentos_ativos_Servidor1[$y]}" repetido na bat, removido" |tee -a $LOG
						mapeamentos_ativos_Servidor1[$y]=`date +%H%M%S`$i
						((i++))
						x=0
						y=0
					fi
					
				let "y=y+1"
				done

			let "x=x+1"
			done

			####     Servidor2     ####

			x=0
			y=0

			#conferindo Servidor2
			for x in `echo ${!recursos_Servidor21[*]}`;do
				liberado=0
				for y in `echo ${!mapeamentos_ativos_Servidor2[*]}`;do
					if [ ${recursos_Servidor21[$x]} == ${mapeamentos_ativos_Servidor2[$y]} ]; then
						liberado=1
						#Tirando o comentario caso haja
						sed -i '/'${recursos_Servidor21[$x]}'/ s/rem //g' /home/netlogon/$user".bat"
						y=`echo ${!mapeamentos_ativos_Servidor2[*]}`
					fi
				done
				if [ $liberado != 1 ]; then
					#recurso nao consta na bat, liberar
					echo "Recurso "${recursos_Servidor21[$x]}" nao consta na bat, liberando"  |tee -a $LOG
					echo 'net use '${letras_livres[$z]}': \\Servidor2\'${recursos_Servidor21[$x]}' '$SenhaLogin' /user:'$user' /persistent:no' >> /home/netlogon/$user".bat"
					let "z = z + 1"
				fi
			done

			x=0
			y=0

			#agora confere se tem algo na bat que nao tem liberado no Servidor2
			for x in `echo ${!mapeamentos_ativos_Servidor2[*]}`;do
				liberado=0
				for y in `echo ${!recursos_Servidor21[*]}`;do
					if [ ${recursos_Servidor21[$y]} == ${mapeamentos_ativos_Servidor2[$x]} ]; then
						liberado=1
						y=`echo ${!recursos_Servidor21[*]}`
					fi
				done
				if [ $liberado != 1 ]; then
					#recurso nao esta liberado no servidor, removendo da bat
					echo "Recurso "${mapeamentos_ativos_Servidor2[$x]}" nao esta liberado no servidor, removendo da bat" |tee -a $LOG
					sed -i '/'${mapeamentos_ativos_Servidor2[$x]}'/d'  /home/netlogon/$user".bat"
				fi
			done

			x=0
			y=0

			#remove mapeamentos repetidos do Servidor2, deixando apenas um

			x=0
			y=0
			i=0

			while [ $x -le ${#mapeamentos_ativos_Servidor2[*]} ]; do

				y=$x
				while [ $y -le ${#mapeamentos_ativos_Servidor2[*]} ]; do

					if [ "${mapeamentos_ativos_Servidor2[$x]}" == "${mapeamentos_ativos_Servidor2[$y]}" ] && [ $x != $y ]; then
						sed -i '/'${mapeamentos_ativos_Servidor2[$y]}'/{H;x;/^\n/d;g;}' /home/netlogon/$user".bat"
						echo "Mapeamento "${mapeamentos_ativos_Servidor2[$y]}" repetido na bat, removido." |tee -a $LOG
						mapeamentos_ativos_Servidor2[$y]=`date +%H%M%S`$i
						((i++))
						x=0
						y=0
					fi

				let "y=y+1"
				done

			let "x=x+1"
			done

			######################### ajustes gerais na bat #####################

			#verifica se alguma letra esta sendo utilizada em redundancia
			mapeamentos_usados=(${mapeamentos_usados[@]} `sed '/user:/!d;/rem/ s/rem //g' /home/netlogon/$user.bat | cut -d" " -f3 | tr -d :`)

			mapeamentos_comentados=2
			VerificaLetrasDisponiveis

			x=0
			y=0
			i=0

			while [ $x -le ${#mapeamentos_usados[*]} ]; do

				y=$x
				while [ $y -le ${#mapeamentos_usados[*]} ]; do

					if [ "${mapeamentos_usados[$x]}" == "${mapeamentos_usados[$y]}" ] && [ $x != $y ]; then
						sed -i ':a;$!{N;ba;};s/\(.*\)'${mapeamentos_usados[$y]}': /\1'${letras_livres[$i]}': /' /home/netlogon/$user".bat"
						echo "trocando a letra "${mapeamentos_usados[$y]}" por "${letras_livres[$i]} |tee -a $LOG
						mapeamentos_usados[$y]=${letras_livres[$i]}
						let "i=i+1"
						x=0
						y=0
					fi

				let "y=y+1"
				done

			let "x=x+1"
			done

			#####

			#atualiza de fato os mapeamentos tratados ate agora na bat
			mapeamentos_ativos=$(sed '/user:/!d;/persistent:/!d' /home/netlogon/$user".bat" )
			echo "$mapeamentos_ativos" | sort >> /home/netlogon/$user"_new.bat"
			echo " " >> /home/netlogon/$user"_new.bat"
		fi
		
		######################### fim da parte dos mapeamentos e inicio do rodape     #########################

		#outras coisas	
		echo "$mapeamentos_outros" | sort >> /home/netlogon/$user"_new.bat"
		echo " " >> /home/netlogon/$user"_new.bat"
		
		#coloca as informacoes de mapeamento para caso haja problema com o adc dc
		echo "rem \\\Servidor1\public\deploy\deploy.bat" >> /home/netlogon/$user"_new.bat"
		echo "rem \\\Servidor1\public\deploy\backinfo\backInfo.exe" >> /home/netlogon/$user"_new.bat"
		echo " " >> /home/netlogon/$user"_new.bat"

		#insere a informacao da migracao da bat
		echo $migrado >> /home/netlogon/$user"_new.bat"
		
		#finalizando a bat
		mv /home/netlogon/$user"_new.bat" /home/netlogon/$user".bat"
		
		#converte e disponibiliza a bat onde o DC pega
		unix2dos /home/netlogon/$user".bat"
		cp /home/netlogon/$user".bat" /root/shellpro/migraUsuario/BatsMigradas/
		
		echo "Bat autalizada com sucesso" |tee -a $LOG
		
		rm -rf /home/netlogon/$user"_new.bat"
		rm -rf /home/netlogon/$user".bat"
		
	fi
}

# 10 - Procedure usada quando deseja-se excluir um mapeamento em especifico
ExcluirUnicoMapeamento(){
 	#verifica se o usuario esta cadastrado no Servidor2
	TestaUsuarioExisteServidor2

	echo "Voce esta removendo um recurso de $name" |tee -a $LOG
	echo " "
	
	#informacao do servidor em que sera removido o recurso
	echo " "
	echo " Escolha o servidor"
	echo " "
	echo " 1 - Servidor2"
	echo " 2 - Servidor1"
	echo " 3 - Cancelar"		
	read servidor

	#pega o nome do recurso
	echo "Informe o recurso a ser removido"	| tee -a $LOG
	read smb
	smb=$(echo "$smb" | tr 'A-Z' 'a-z' | sed 's/ //g')

	echo "OPC de Servidor "$servidor" recurso "$smb >> $LOG

	case $servidor in
		1|s|S)server="Servidor2"
			compsamb=`ssh Servidor2 cat /etc/group | grep -w $smb"_rx" | rev | cut -f2-5 -d"_" | rev | grep -v -`
			if [ "$smb" = "$compsamb" ]; then
				localizou=1
			else
				localizou=0
			fi						
		;;

		2|l|L)server="Servidor1"
			compsamb=`cat /etc/group | grep -w $smb"_rx" | rev | cut -f2-5 -d"_" | rev | grep -v -`
			if [ "$smb" = "$compsamb" ]; then
				localizou=1
			else
				localizou=0
			fi						
		;;

		*)  echo "Operacao Cancelada!" |tee -a $LOG
			echo " "
			exit
		;;
	esac

	#verifica se o recurso existe no servidor
	if [ "$localizou" == 0 ]; then
		echo "Recurso nao foi localizado."|tee -a $LOG
	else
		#ira perguntar o tipo de liberacao a ser removida	
		echo "Informar o tipo de acesso:"
		echo " "
		echo "1 - Leitura RX"
		echo "2 - Gravacao RW"
		echo " "
		read opcao
		echo "OPC de permissao do recuro "$opcao >> $LOG

		case $opcao in
			1|rx|RX|rX|Rx)liberacao=rx
			liberacao_inversa=rw			
			;;

			2|rw|RW|rW|rW)liberacao=rw
			liberacao_inversa=rx
			;;

			*)echo -e "Opcao Invalida de acesso!" |tee -a $LOG
			echo " "
			exit
			;;

		esac

		#verifica se o usuario ja possui o recurso liberado, removendo o de permissao diferente quando aplicado e adicionando o servidor (no Servidor2)
		if [ $server == "Servidor2" ]; then
			
			COUNT3=`ssh Servidor2 cat /etc/group | sed 's/^\|$\|:/,/g' | grep ,$user, | grep $smb'_'$liberacao | wc -l`			
			
			if [ $COUNT3 == 1 ]; then
			
				ssh Servidor2 gpasswd -d $user $smb"_"$liberacao
				echo "Recurso "$smb"_"$liberacao" Removido" |tee -a $LOG
				
			else
			
				echo "Usuario nao possui o recurso informado. Nada foi feito"|tee -a $LOG
				COUNT4=`ssh Servidor2 cat /etc/group | sed 's/^\|$\|:/,/g' | grep ,$user, | grep $smb'_'$liberacao_inversa | wc -l`			
				
				if [ $COUNT4 == 1 ]; then
					
					echo "Foi localizado o acesso ao recurso "$smb" com o tipo de acesso "$liberacao_inversa". Deseja excluir essa liberacao? S/N" |tee -a $LOG
					read resp
					
					if [ $resp == "S" -o $resp == "s" ];then
						ssh Servidor2 gpasswd -d $user $smb"_"$liberacao_inversa
						echo "Recurso "$smb"_"$liberacao_inversa" Removido" |tee -a $LOG
					fi
	
				fi

			fi
			
		#verifica se o usuario ja possui o recurso liberado, removendo o de permissao diferente quando aplicado e adicionando o servidor (no Servidor1)
		else
			COUNT3=`cat /etc/group | sed 's/^\|$\|:/,/g' | grep ,$user, | grep $smb'_'$liberacao | wc -l`			
			
			if [ $COUNT3 == 1 ]; then
			
				gpasswd -d $user $smb"_"$liberacao
				echo "Recurso "$smb"_"$liberacao" Removido" |tee -a $LOG
				
			else
			
				echo "Usuario nao possui o recurso informado. Nada foi feito"|tee -a $LOG
				COUNT4=`cat /etc/group | sed 's/^\|$\|:/,/g' | grep ,$user, | grep $smb'_'$liberacao_inversa | wc -l`			
				
				if [ $COUNT4 == 1 ]; then
					
					echo "Foi localizado o acesso ao recurso "$smb", porem com o tipo de acesso "$liberacao_inversa". Deseja excluir essa liberacao? S/N" |tee -a $LOG
					read resp
					
					if [ $resp == "S" -o $resp == "s" ];then
						gpasswd -d $user $smb"_"$liberacao_inversa
						echo "Recurso "$smb"_"$liberacao_inversa" Removido" |tee -a $LOG
					fi
	
				fi

			fi
		fi

		ReorganizaBat

	fi
}

# 11 - Procedure de Entrada de InformacÃµes sobre o Usuario - Todos os mapeamentos e BAT
InformacaoUsuarioMapeamentos(){

	#vai exibir os mapeamentos da bat do usuario, que foram salvos no arquivo de log abaixo

	echo " "
	echo " O login $user ( $name ) tem os seguites mapeamentos na BAT:" |tee -a $LOG
	echo " "
	
	#pega os mapeamentos na bat
	sed '/user:/!d;/rem\|REM\|Rem/d;/Servidor1\\'$user'/d' /root/shellpro/migraUsuario/BatsMigradas/$user".bat" |tee -a $LOG
	echo ""
	echo " Mapeamentos Inativos:" |tee -a $LOG
	echo ""
	sed '/user:/!d;/rem\|REM\|Rem/!d' /root/shellpro/migraUsuario/BatsMigradas/$user".bat" |tee -a $LOG
	
	echo " "
	echo " Ele possui os acessos abaixo habilitados nos servidores:" |tee -a $LOG
	echo " "
	echo " Servidor2:"
	
	#pega os recursos do samba liberados para o usuario no Servidor2
	recursos_Servidor2=(${recursos_Servidor2[@]} `ssh Servidor2 "cat /etc/group | sed 's/^\|$\|:/,/g' | grep ',$user,' | cut -d',' -f2" | grep -v '#'`)

	if [ -z $recursos_Servidor2 ]; then
		echo ""
		echo "Nenhum recurso no Servidor2" |tee -a $LOG
	else
		for x in `echo ${!recursos_Servidor2[*]}`;do
			echo ${recursos_Servidor2[$x]} |tee -a $LOG
		done
	fi
	
	echo " "
	echo " Servidor1:"
	
	#pega os recursos do samba liberados para o usuario no Servidor1
	recursos_Servidor1=(${recursos_Servidor1[@]} `cat /etc/group | sed 's/^\|$\|:/,/g' | grep ",$user," | cut -d',' -f2 | grep -v '#'`)

	if [ -z $recursos_Servidor1 ]; then
		echo ""
		echo "Nenhum recurso no Servidor1" |tee -a $LOG
	else
		for x in `echo ${!recursos_Servidor1[*]}`;do
			echo ${recursos_Servidor1[$x]} |tee -a $LOG
		done
	fi
}

# 12 - Procedure para alterar quota de usuario
AlterarQuota (){
	echo "A atual quota do usuario e: " $(ExibeQuota) | tee -a $LOG
	echo " "
	echo "Informe qual o novo padrao de quota"
	echo " "
	echo " 1 - Padrao (977M)"
	echo " 2 - Assessor (1465M)"
	echo " 3 - Chefe (1954M)"
	echo " 4 - Gerente (2930M)"
	echo " 5 - Diretor (2930M)"
	echo " "
	read opc_quota
	
	echo $opc_quota >> $LOG
	
	case $opc_quota in
	
		1|p|P) quota_app=padrao1
		;;
		2|a|A) quota_app=assessor_pad
		;;
		3|c|C) quota_app=chefe_pad
		;;
		4|g|G) quota_app=gerente_pad
		;;
		5|d|D) quota_app=diretor_pad
		;;
		*) echo " Opcao Invalida!" | tee -a $LOG
        echo " "
        exit
		;;
	esac
	
	#altera a quota
	edquota -p $quota_app $user
	
	echo "A quota foi alterada para: " $(ExibeQuota) | tee -a $LOG
}

# 13 - Procedure para liberar recursos de rede para varios usuarios
LiberaRecursosLote (){
	echo "nada"
	exit
	echo "Informe a letra que deseja liberar para todos os colaboradores" |tee -a $LOG
	read letra
	echo $letra >>$LOG
	
	#informacao do servidor em que sera liberado o recurso
	echo " "
	echo " Escolha o servidor"
	echo " "
	echo " 1 - Servidor2"
	echo " 2 - Servidor1"
	echo " 3 - Cancelar"		
	read servidor

	#pega o nome do recurso
	echo "Informe o recurso a ser liberado"|tee -a $LOG			
	read smb
	smb=$(echo "$smb" | tr 'A-Z' 'a-z' | sed 's/ //g')

	echo $servidor" "$smb >> $LOG
	
	case $servidor in
		1|s|S)server="Servidor2"
			compsamb=`ssh Servidor2 cat /etc/group | grep -w $smb"_rx" | rev | cut -f2-5 -d"_" | rev | grep -v -`
			if [ "$smb" = "$compsamb" ]; then
				localizou=1
			else
				localizou=0
			fi						
		;;

		2|l|L)server="Servidor1"
			compsamb=`cat /etc/group | grep -w $smb"_rx" | rev | cut -f2-5 -d"_" | rev | grep -v -`
			if [ "$smb" = "$compsamb" ]; then
				localizou=1
			else
				localizou=0
			fi						
		;;

		*)  echo -e "Operacao Cancelada!" |tee -a $LOG
			echo " "
			exit
		;;
	esac

	#verifica se o recurso existe no servidor
	if [ "$localizou" == 0 ]; then
		echo "Recurso nao foi localizado." |tee -a $LOG
	else
		#######colocar codigo para verificar se o usuario ja tem a liberacao	
		echo "Informar o tipo de acesso:" |tee -a $LOG
		echo " "
		echo "1 - Leitura RX"
		echo "2 - Gravacao RW"
		echo " "
		read opcao
		echo $opcao >> $LOG

		case $opcao in
			1|rx|RX|rX|Rx)liberacao=rx
			liberacao_inversa=rw
			;;

			2|rw|RW|rW|rW)liberacao=rw
			liberacao_inversa=rx
			;;

			*)echo -e "Opcao Invalida de acesso!" |tee -a $LOG
			echo " "
			exit
			;;

		esac
		
		#pergunta os usuarios
		
		
		exit
		#verifica se o usuario ja possui o recurso liberado, removendo o de permissao diferente quando aplicado e adicionando o servidor
		if [ $server == "Servidor2" ]; then
			COUNT3=`ssh Servidor2 cat /etc/group | sed 's/^\|$\|:/,/g' | grep ,$user, | grep $smb'_'$liberacao | wc -l`			
			if [ $COUNT3 == 1 ]; then
				echo "Recurso ja esta liberado"|tee -a $LOG
				ReorganizaBat
			else
				COUNT4=`ssh Servidor2 cat /etc/group | sed 's/^\|$\|:/,/g' | grep ,$user, | grep $smb'_'$liberacao_inversa | wc -l`
				if [ $COUNT4 == 1 ]; then
					echo "Removendo atual acesso com permissao difetente"|tee -a $LOG
					ssh Servidor2 gpasswd -d $user $smb"_"$liberacao_inversa
				fi
				ssh Servidor2 gpasswd -a $user $smb"_"$liberacao
				echo "Recurso foi Liberado"|tee -a $LOG
			fi
		else
			COUNT3=`cat /etc/group | sed 's/^\|$\|:/,/g' | grep ,$user, | grep $smb'_'$liberacao | wc -l`			
			if [ $COUNT3 == 1 ]; then
				echo "Recurso ja esta liberado"|tee -a $LOG
				ReorganizaBat
			else
				COUNT4=`cat /etc/group | sed 's/^\|$\|:/,/g' | grep ,$user, | grep $smb'_'$liberacao_inversa | wc -l`
				if [ $COUNT4 == 1 ]; then
					echo "Removendo atual acesso com permissao difetente"|tee -a $LOG
					gpasswd -d $user $smb"_"$liberacao_inversa
				fi
				gpasswd -a $user $smb"_"$liberacao
				echo "Recurso foi Liberado"|tee -a $LOG
			fi
		fi

		if [ $COUNT3 != 1 ]; then

			#copia a bat para uma area de uso temporario
			cp /root/shellpro/migraUsuario/BatsMigradas/$user".bat" /home/netlogon
			#converte a bat para uso do linux
			dos2unix /home/netlogon/$user".bat"
			#pega a senha da bat, insere a linha do novo recurso liberado e ordena por ordem alfabetica
			SenhaLogin=$(sed '/Servidor1\\'$user'/!d' /home/netlogon/$user".bat" | cut -f5 -d" ")
			echo 'net use '$letra': \\'$server'\'$smb' '$SenhaLogin' /user:'$user' /persistent:no' >> /home/netlogon/$user".bat"

			unix2dos /home/netlogon/$user".bat"
			cp /home/netlogon/$user".bat" /root/shellpro/migraUsuario/BatsMigradas/
			rm -f /home/netlogon/$user".bat"
			ReorganizaBat
		fi
	fi
}

# 0 - Procedure que exibe na tela as funcoes do script




Apresenta (){

		clear
		
		echo -e " +------------------------------------------------------------+"
		echo -e " | Rotina de Manutencao de Usuarios em Servidores             |"
		echo -e " | e Servicos Mantidos pelo SCOP                              |"
		echo -e " |                                                            |"
		echo -e " | ----------------------------                               |"
		echo -e " | Ambiente de PROD                                           |"
		echo -e " | ----------------------------                               |"
		echo -e " +------------------------------------------------------------+"
		echo -e " | O que voce deseja executar?                                |"
		echo -e " |                                                            |"
		echo -e " | 01 - Revisao (revogacao) de Recursos de Rede               |"
		echo -e " | 02 - Informacao do usuario Resumida                        |"
		echo -e " | 03 - Criacao de usuario no Servidor1 e Servidor2                  |"
		echo -e " | 04 - Criacao de usuario no Servidor2                           |"
		echo -e " | 05 - Desligamento de Usuario                               |"
		echo -e " | 06 - Pesquisa de usuario aprovador                         |"
		echo -e " | 07 - Liberacao de Recursos de Rede para um Usuario         |"
		echo -e " | 08 - Restabelecimento de Acesso                            |"
		echo -e " | 09 - Atualiza Bat                                          |"
		echo -e " | 10 - Excluir um unico mapeamento de rede                   |"
		echo -e " | 11 - Informacao dos Mapeamentos e BAT do Usuario           |"
		echo -e " | 12 - Alterar quota de usuario                              |"
		#echo -e " | 13 - Liberar recursos em lote                              |"
		echo -e " |                                                            |"
		echo -e " | Entre com a opcao:                                         |"
		echo -e " +------------------------------------------------------------+"

		#recebe o usuario
		read OPC
		echo "Operacao "$OPC >>$LOG
		echo " "

		#chama a procedure que verifica se o usuario existe, abortando caso nao exista, a nao ser que seja escolhido a criacao de login
		if [ $OPC != 3 ] ; then 
				if [ $OPC != 13 ]; then
			
				echo " Informe o login do usuario: " |tee -a $LOG
				read user

				# deixa o usuario todo em minusculo e retira espacos e caracteres especiais, menos . e - _
				user=$(echo "$user"| tr 'A-Z' 'a-z' | sed 's/[\ *$?#@!%&,"]//g')
				echo $user >>$LOG
				
				name=`cat /etc/passwd | grep "/$user:" | cut -d":" -f5`
				TestaUsuarioExisteServidor1
			fi
		fi

		case $OPC in

			1|"01") ExclusaoMap
			;;
			2|"02") InformacaoUsuarioResumida
			;;
			3|"03") CriacaoUsuarioServidor1
			;;
			4|"04") CriacaoUsuarioServidor2
			;;
			5|"05") ExclusaoUsuario
			;;
			6|"06") PesquisaAprovador
			;;
			7|"07") LiberacaoRecursos
			;;
			8|"08") RestAcesso
			;;
			9|"09") ReorganizaBat
			;;
			10) ExcluirUnicoMapeamento
			;;
			11) InformacaoUsuarioMapeamentos
			;;
			12) AlterarQuota
			;;
			#13) LiberaRecursosLote
			#;;
			*) echo " Opcao Invalida!" | tee -a $LOG
			echo " "
			exit
			;;

		esac 
		
		
		
		


}

################## FIM DA SECAO DE PROCEDURES ###########################
###################### INICIO DO PROGRAMA ###############################

	echo "Inicio do programa "$$ > $LOG
	who m i >>$LOG
	echo $data >>$LOG
	date >>$LOG
	clear
	Apresenta
	echo " "
	echo "Programa finalizado com sucesso" | tee -a $LOG
	
	echo "Deseja voltar ao menu S ou N"
		echo " "
		read Volta

	if [ "$Volta" = "s" ]; then
		/root/shellpro/operacao/network_manager.sh
	fi
