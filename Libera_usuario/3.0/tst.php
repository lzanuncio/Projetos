<font face = "Arial" size = 3 >
<?php

//Logins que não funcionam quando verificadas pelo Dominio
$usu_ter = array('schenker1.ext', 'schenker2.ext');
$pss_ter = array('teste123', 'teste321');

if ($_GET['acao'] == "libera"){
	ldap_unbind($cnx);

	$arquivo = "logs/contador.txt";
	$handle = fopen ($arquivo, 'r+');
	$data = fread ($handle, 512);	
	$contador = $data + 1;
  
  
	$ipaddress = '';

    if ($_SERVER['HTTP_CLIENT_IP']){
        $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
	}
    else if($_SERVER['HTTP_X_FORWARDED_FOR']){
        $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
	}
    else if($_SERVER['HTTP_X_FORWARDED']){
        $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
	}
    else if($_SERVER['HTTP_FORWARDED_FOR']){
        $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
	}
    else if($_SERVER['HTTP_FORWARDED']){
        $ipaddress = $_SERVER['HTTP_FORWARDED'];
	}
    else if($_SERVER['REMOTE_ADDR']){
        $ipaddress = $_SERVER['REMOTE_ADDR'];
	}
    else {
        $ipaddress = 'UNKNOWN';
	}
	
	$LDAPUser = $_POST["login"];
	$LDAPUserPassword = $_POST["senha"];
	$ambiente = $_POST["sistema"];
	$log_date = date('d-m-Y');
	$log_time = date('H:i:s');
	
	$LDAPUser = strtolower($LDAPUser);
  
	if ($ambiente == "ECOMEX"){
		$lgfinal = fopen("logs/ecomex/kecomex_automatic.html", "r+");
		$log_final = fwrite($lgfinal, "#################### Last Update ".$log_data."(".$log_time.") (Brazil/Sao Paulo) ####################<br>############################## Numero total de Visitas: ".$contador." ###############################");
		fclose($lgfinal);
	} else {
		$lgfinal = fopen("logs/oracle/oracle_automatic.html", "r+");
		$log_final = fwrite($lgfinal, "#################### Last Update ".$log_data."(".$log_time.") (Brazil/Sao Paulo) ####################<br>############################## Numero total de Visitas: ".$contador." ###############################");
		fclose($lgfinal);
	}
	
	/*if ($LDAPUser == "" || $LDAPUserPassword == ""){
		$texto = "<p>91 - Favor inserir seu login e senha antes de clicar em liberar!
				 <p><font color=\"red\"><b>O login e senha devem ser os mesmos usados para logar no Windows.</b></font>";
	} 
  
	else { 
	*/	
		for($i = 0; $i < count($usu_ter); $i++){
				if($LDAPUser == $usu_ter[$i] && $LDAPUserPassword == $pss_ter[$i]){
					$x = 1;
				}
			}
					
		if($x != 1){
			session_start();
			if ($_SESSION['login'] != ""){
				$x = 1;		
				session_destroy();
			}
		}
		
		if($x != 1){
		$SearchField="samaccountname";
		$LDAPHost = "dc.romi.com";
		$dn = "DC=romi,DC=com";
		$LDAPUserDomain = "@romi.com";
		
		$LDAPFieldsToFind = array("cn", "givenname", "samaccountname", "homedirectory", "telephonenumber", "mail");
		$cnx = ldap_connect($LDAPHost);
														
		ldap_set_option($cnx, LDAP_OPT_PROTOCOL_VERSION, 3);
		ldap_set_option($cnx, LDAP_OPT_REFERRALS, 0);
		 
		ldap_bind($cnx,$LDAPUser.$LDAPUserDomain,$LDAPUserPassword);
		error_reporting (E_ALL ^ E_NOTICE);
		  
		$filter="($SearchField=$LDAPUser)";
		$sr=ldap_search($cnx, $dn, $filter);
		$info = ldap_get_entries($cnx, $sr);

		$x = $info["count"];
		}

		
		if($x == 1){
			$nam=$info[0]['cn'][0];
			$sam=$LDAPUser;
			
			
			foreach($info[0]['memberof'] as $grps){ 
				$gruposs = explode(" CN=", $grps);
				foreach($gruposs as $verfgrp){
							
					if ($verfgrp <> "") {					
						if  ($verfgrp == "CN=Temp_scop,OU=SCOP,OU=DSIS,OU=DAF,OU=PD,OU=Departments,DC=romi,DC=com"){
							session_start();
							$_SESSION['login'] = $sam;
							header('Location: operador.php');
						} 
					}
				}
			}
			
/*			fseek($handle, 0);
			fwrite($handle, $contador);
			fclose($handle);
*/
			//$executa = shell_exec("sudo -u root sh ./xx_user_freedom.sh $sam $ambiente");
			echo "sudo -u root sh ./xx_user_freedom.sh $sam $ambiente";
			if ($executa == ""){
/*				$arquivo2 = "logs/qerro.txt";

				$handle = fopen ($arquivo2, 'r+');

				$data2 = fread ($handle, 512);
	
				$erros = $data2 + 1;
			
				fseek($handle, 0);
				fwrite($handle, $erros);
				fclose($handle);
			
				if ($erros >= "5"){
					$headers =  "Content-Type:text/html; charset='UTF-8'\n";
					$headers .= "From:  <scop@romi.com>\n"; //Vai ser //mostrado que  o email partiu deste email e seguido do nome
					$headers .= "X-Sender:  <scop@romi.com>\n"; //email do servidor //que enviou
					$headers .= "X-Mailer: PHP  v".phpversion()."\n";
					$headers .= "X-IP:  ".$_SERVER['REMOTE_ADDR']."\n";
					$headers .= "Return-Path:  <scop@romi.com>\n"; //caso a msg //seja respondida vai para  este email.
					$headers .= "MIME-Version: 1.0\n";
				
					$para = "scop@romi.com";
					$assuntoemail = "Libera Usuários";
					$mensagem = "Senhores,<br><br>O libera usuário esta apresentando erro diversas vezes seguidas, por favor verificar";
				
					mail($para, $assuntoemail, $mensagem, $headers);  //função que faz o envio do email.				
				}
*/				$texto = "<p>$controla_erro 021-Não foi possivel desbloquear! Favor tente novamente.
						<p>Caso o problema persista, contate o SCOP através dos ramais 9960/9961.";

			} else {
/*				if($ambiente == "ORACLE"){
					$amb = "PRO";
				} else if ($ambiente == "DEV-ORACLE"){
					$amb = "DEV";
				}
				
				if($ambiente == "ECOMEX"){
					$fp = fopen("logs/ecomex/kecomex_automatic.html", "a");
					$log_libera = fwrite($fp, "<tr><td>".$ipaddress."</td><td>".$log_date."</td><td>".$log_time."</td><td>".$executa."</td><td>".$sam."</td></tr>\n");
					fclose($fp);
				} else {
					$fp = fopen("logs/oracle/oracle_automatic.html", "a");
					$log_libera = fwrite($fp, "<tr><td>".$amb."</td><td>".$ipaddress."</td><td>".$log_date."</td><td>".$log_time."</td><td>".$executa."</td><td>".$sam."</td></tr>\n");
					fclose($fp);
				}
				
				$arquivo2 = "logs/qerro.txt";

				$handle = fopen ($arquivo2, 'r+');

				$data2 = fread ($handle, 512);
		
				$erros = 0;
				
				fseek($handle, 0);
				fwrite($handle, $erros);
				fclose($handle);
						
				$result_cont = $contador / 100;
				if (is_int($result_cont)){
					$headers =  "Content-Type:text/html; charset='UTF-8'\n";
					$headers .= "From:  <scop@romi.com>\n"; //Vai ser //mostrado que  o email partiu deste email e seguido do nome
					$headers .= "X-Sender:  <scop@romi.com>\n"; //email do servidor //que enviou
					$headers .= "X-Mailer: PHP  v".phpversion()."\n";
					$headers .= "X-IP:  ".$_SERVER['REMOTE_ADDR']."\n";
					$headers .= "Return-Path:  <scop@romi.com>\n"; //caso a msg //seja respondida vai para  este email.
					$headers .= "MIME-Version: 1.0\n";
					
					$para = "scop@romi.com";
					$assuntoemail = "Libera Usuários";
					$mensagem = "Senhores,<br><br>Já foram desbloqueados $contador usuários.";
					
					mail($para, $assuntoemail, $mensagem, $headers);  //função que faz o envio do email.
				}
*/				
				$texto = "<p>Usuário liberado com sucesso!
						<p>Você é o visitante numero: $contador";
			}
		} else {
			$texto = "031-Login ou senha invalidos, Por favor tente novamente!
					<p>Caso o problema persista, contate o SCOP através dos ramais 9960/9961.
					<p><font color=\"red\"><b>O login e senha devem ser os mesmos usados para logar no Windows.</b></font>";
		}
	//}
}
?>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
		<title>Auto-Liberar</title>			
		<script>
			function carrega(){
				var name = document.getElementById('pname').value;
				var password = document.getElementById('senha').value;
				if (name != "" && password != ""){	
					document.getElementById('blanket').style.display = 'block';
					document.getElementById('aguarde').style.display = 'block';
					document.getElementById('body').style.cursor = 'progress';
				}
			}
		</script>
		<link href="css/bootstrap.min.css" rel="stylesheet">
		<script src="js/jquery.js"></script>
		<script src="js/bootstrap.min.js"></script>	
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.12.4/css/bootstrap-select.min.css">		
		<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.12.4/js/bootstrap-select.min.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.12.4/js/i18n/defaults-*.min.js"></script>
		<!--[if IE]><link rel="stylesheet" type="text/css" href="css/ie.css"/><!-- <![endif]-->
		<!--[if !IE]>--><link rel="stylesheet" type="text/css" href="css/chrome_tst.css"/><!-- <![endif]-->
		
	</head>
	<body id="body" class="body" onload="login.value='';senha.value=''">
		<div class="posicao" id="login">
			<div class="ihelp">
				<img src="image/help.png" width="32px" height="32px">
				<span class="help">
					<p><font color="red"><b>Para liberar, seguir os passos abaixo após fechar o Oracle ou E-Comex:</font>
					<p>Primeiramente, escolha o sistema, ensira seu login e senha (a mesma usada para logar no Windows).
					<p><font color="red">Em seguida, apertar o botão "LIBERAR" e aguardar a conclusão.</font>
					<p>Em caso de dúvidas, contate o SCOP através dos ramais 9960/9961</b>
				</span>
			</div>
			<p><b><font face="Arial" size="4" color="navy" >
			<center>LIBERA USUÁRIO</font></b> 
				<p><?php echo $texto; ?>
			</center>
			<form action="tst.php?acao=libera" method="post" target="_self" onsubmit="enviar.disabled=true">
				<div class="divlogin">
					
							<center>
								<select name="sistema" class="selectpicker">
									<option disabled>Selecione o Sistema</option>
									<option value="ORACLE" selected>ORACLE</option>
									<option value="ECOMEX">ECOMEX</option>
									<option value="DEV-ORACLE">DEV-ORACLE</option>
								</select> 
							</center>
		
					<div class="tblogin" style="margin-top: 10px;">
						<img class="imagem" src="image/login.png"><input placeholder="Usuário" class="campo" type="text" name="login" size="20" id="pname" required/>
					</div>
					<div class="tblogin">
						<img class="imagem" src="image/pass.png"><input placeholder="Senha" class="campo" type="password" name="senha" size="20" id="senha" required/>
					</div>
					<div>
						<center>
							<input type="submit" class="btn btn-default" name="enviar" onclick="carrega()" value="Liberar"/>
						</center>
					</div>
				</div>
			</form>
		</div>     
		<center>
			<div id="blanket"></div>
			<div id="aguarde"></div>
		</center>
	</body>
</html>
</font>
