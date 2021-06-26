<font face = "Arial" size = 3 >
<?php
	
  ldap_unbind($cnx);

  $arquivo = "logs/contador.txt";

  $handle = fopen ($arquivo, 'r+');

  $data = fread ($handle, 512);
	
  $contador = $data + 1;
	
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Last Modified: '. gmdate('D, d M Y H:i:s') .' GMT');
header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
header('Pragma: no-cache');
header("Cache: no-cache");
header('Expires: 0');

 $ipaddress = '';

     if ($_SERVER['HTTP_CLIENT_IP'])
         $ipaddress = $_SERVER['HTTP_CLIENT_IP'];

     else if($_SERVER['HTTP_X_FORWARDED_FOR'])
         $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];

     else if($_SERVER['HTTP_X_FORWARDED'])
         $ipaddress = $_SERVER['HTTP_X_FORWARDED'];

     else if($_SERVER['HTTP_FORWARDED_FOR'])
         $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];

     else if($_SERVER['HTTP_FORWARDED'])
         $ipaddress = $_SERVER['HTTP_FORWARDED'];

     else if($_SERVER['REMOTE_ADDR'])
         $ipaddress = $_SERVER['REMOTE_ADDR'];

     else
         $ipaddress = 'UNKNOWN';

  $LDAPUser = $_POST["login"];
  $LDAPUserPassword = $_POST["senha"];
  $search_submitted = $LDAPUser;
  $ambiente = $_POST["sistema"];
  
  if ($LDAPUser == "" || $LDAPUserPassword == ""){
	  chama_tela("<p>91 - Favor inserir seu login e senha antes de clicar em liberar!
				 <p><font color=\"red\"><b>O login e senha devem ser os mesmos usados para logar no Windows.</b></font>");
  } 
  
  else { 
  
	  $SearchFor = $search_submitted;
	  $SearchField="samaccountname";

		$LDAPHost = "dc.romi.com";
		$dn = "DC=romi,DC=com";
		$LDAPUserDomain = "@romi.com";
		
	  $LDAPFieldsToFind = array("cn", "givenname", "samaccountname", "homedirectory", "telephonenumber", "mail");

	  $cnx = ldap_connect($LDAPHost) or die(chama_tela("011-Urgente! Contate o SCOP atarvés dos ramais 9960/9961"));
														
	  ldap_set_option($cnx, LDAP_OPT_PROTOCOL_VERSION, 3);
	  ldap_set_option($cnx, LDAP_OPT_REFERRALS, 0);
	 
	  ldap_bind($cnx,$LDAPUser.$LDAPUserDomain,$LDAPUserPassword) or die(chama_tela("031-Login ou senha invalidos, Por favor tente novamente!
																					<p>Caso o problema persista, contate o SCOP através dos ramais 9960/9961.
																					<p><font color=\"red\"><b>O login e senha devem ser os mesmos usados para logar no Windows.</b></font>"));
	  error_reporting (E_ALL ^ E_NOTICE);
	  
	  $filter="($SearchField=$SearchFor)";
	  $sr=ldap_search($cnx, $dn, $filter);
	  $info = ldap_get_entries($cnx, $sr);

	  $x = $info["count"];

  if($x == 1){
	$nam=$info[0]['cn'][0];
	$sam=$info[0]['samaccountname'][0];
	
		//Cria um laço onde $grps se recebe o nome dos grupos que o usuário pertence
		foreach($info[0]['memberof'] as $grps){ 
			$gruposs = explode(" CN=", $grps);
			foreach($gruposs as $verfgrp){
				if ($verfgrp <> "") {
					if  ($verfgrp == "CN=Temp_scop,OU=SCOP,OU=DSIS,OU=DAF,OU=PD,OU=Departments,DC=romi,DC=com"){
						chama_tela_op();
						exit;
					}			
				}
			}
		}
		fseek($handle, 0);
		fwrite($handle, $contador);
		fclose($handle);
		
		//print "Estamos trabalhando... <br>$nam. Por favor aguarde... <br><br>\n";
		$executa = shell_exec("sudo -u root sh ./xx_user_freedom.sh $sam $ipaddress $ambiente $contador");
		if ($executa == ""){
			$arquivo2 = "logs/qerro.txt";

			$handle = fopen ($arquivo2, 'r+');

			$data2 = fread ($handle, 512);
	
			$erros = $data2 + 1;
			
			fseek($handle, 0);
			fwrite($handle, $erros);
			fclose($handle);
			
			if ($erros >= "5"){
				$headers =  "Content-Type:text/html; charset='UTF-8'\n";
				$headers .= "From:  <noreply@romi.com>\n"; //Vai ser //mostrado que  o email partiu deste email e seguido do nome
				$headers .= "X-Sender:  <rfnazatto@romi.com>\n"; //email do servidor //que enviou
				$headers .= "X-Mailer: PHP  v".phpversion()."\n";
				$headers .= "X-IP:  ".$_SERVER['REMOTE_ADDR']."\n";
				$headers .= "Return-Path:  <noreply@romi.com>\n"; //caso a msg //seja respondida vai para  este email.
				$headers .= "MIME-Version: 1.0\n";
				
				$para = "rfnazatto@romi.com";
				$assuntoemail = "Libera Usuários";
				$mensagem = "Senhores,<br><br>O libera usuário esta apresentando erro diversas vezes seguidas, por favor verificar";
				
				mail($para, $assuntoemail, $mensagem, $headers);  //função que faz o envio do email.				
			}
			
			chama_tela("<p>021-Não foi possivel desbloquear! Favor tente novamente.
						<p>Caso o problema persista, contate o SCOP através dos ramais 9960/9961.");
		} else {
			
			$arquivo2 = "logs/qerro.txt";

			$handle = fopen ($arquivo2, 'r+');

			$data = fread ($handle, 512);
	
			$erros = 0;
			
			fseek($handle, 0);
			fwrite($handle, $erros);
			fclose($handle);
					
			$result_cont = $contador / 100;
			if (is_int($result_cont)){
				$headers =  "Content-Type:text/html; charset='UTF-8'\n";
				$headers .= "From:  <noreply@romi.com>\n"; //Vai ser //mostrado que  o email partiu deste email e seguido do nome
				$headers .= "X-Sender:  <rfnazatto@romi.com>\n"; //email do servidor //que enviou
				$headers .= "X-Mailer: PHP  v".phpversion()."\n";
				$headers .= "X-IP:  ".$_SERVER['REMOTE_ADDR']."\n";
				$headers .= "Return-Path:  <noreply@romi.com>\n"; //caso a msg //seja respondida vai para  este email.
				$headers .= "MIME-Version: 1.0\n";
				
				$para = "rfnazatto@romi.com";
				$assuntoemail = "Libera Usuários";
				$mensagem = "Senhores,<br><br>Já foram desbloqueados $contador usuários.";
				
				mail($para, $assuntoemail, $mensagem, $headers);  //função que faz o envio do email.
			}
			
			chama_tela("<p>Usuário liberado com sucesso!
					<p>Você é o visitante numero: $contador");
		}
	}

  else {
	chama_tela("<p>071-O Login $search_submitted não foi encontrado. Por favor tente novamente!
				<p><font color=\"red\"><b>O login e senha devem ser os mesmos usados para logar no Windows.</b></font>");
  }
  }
 function chama_tela($texto){
	echo "
		<html>
			<head>
				<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"/>
				<title>Auto-Liberar</title>		
				<!--[if IE]><link rel=\"stylesheet\" type=\"text/css\" href=\"css/ie.css\"/><!-- <![endif]-->
				<!--[if !IE]>--><link rel=\"stylesheet\" type=\"text/css\" href=\"css/chrome.css\"/><!-- <![endif]-->
			</head>
			<body class=\"body\" onload=\"login.value='';senha.value=''\">

				<div class=\"posicao\" id=\"login\">
				<div class=\"ihelp\">
					<img src=\"image/help.png\" width=\"32px\" height=\"32px\">
					<span class=\"help\">
						<p><font color=\"red\"><b>Para liberar, seguir os passos abaixo após fechar o Oracle ou E-Comex:</font>
						<p>Primeiramente, escolha o sistema, ensira seu login e senha (a mesma usada para logar no Windows).
						<p><font color=\"red\">Em seguida, apertar o botão \"LIBERAR\" e aguardar a conclusão.</font>
						<p>Em caso de dúvidas, contate o SCOP através dos ramais 9960/9961</b>
					</span>
				</div>
					<p><b><font face=\"Arial\" size=\"4\" color=\"navy\" >
					<center>LIBERA USUÁRIO</font></b> 
						<p>$texto
					</center>
					<form action=\"aut.php\" method=\"post\" target=\"_self\" onsubmit=\"enviar.disabled=true\">
						<div class=\"divlogin\">
							<div class=\"tblogin\">
								<div class=\"div-select\">
									<select name=\"sistema\">
										<option disabled>Selecione o Sistema</option>
										<option value=\"ORACLE\" selected>ORACLE</option>
										<option value=\"ECOMEX\">ECOMEX</option>
										<option value=\"DEV-ORACLE\">DEV-ORACLE</option>
									</select> 
								</div>
							</div>			
							<div class=\"tblogin\">
								<img class=\"imagem\" src=\"image/login.png\"><input placeholder=\"Usuário\" class=\"campo\" type=\"text\" name=\"login\" size=\"20\" id=\"pname\"/>
							</div>
							<div class=\"tblogin\">
								<img class=\"imagem\" src=\"image/pass.png\"><input placeholder=\"Senha\" class=\"campo\" type=\"password\" name=\"senha\" size=\"20\" />
							</div>
							<div>
								<input type=\"submit\" class=\"btnColor\" name=\"enviar\" onclick=\"javascript:document.getElementById('blanket').style.display = 'block';document.getElementById('aguarde').style.display = 'block';\" value=\"Liberar\"/>
							</div>
						</div>
					</form>
				</div>     
				<center>
					<div id=\"blanket\"></div>
					<div id=\"aguarde\"></div>
				</center>
			</body>
		</html>
	 ";
 }
 
 function chama_tela_op(){
	echo "
<html>
	<head>
		<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"/>
    		<title>Auto-Liberar</title>	
			<link rel=\"shortcut icon\" href=\"image/favicon.ico\"/>
			<link rel=\"icon\" href=\"image/favicon.png\"/>
			
			
		<!--[if IE]><link rel=\"stylesheet\" type=\"text/css\" href=\"css/ie.css\"/><!-- <![endif]-->
		<!--[if !IE]>--><link rel=\"stylesheet\" type=\"text/css\" href=\"css/chrome.css\"/><!-- <![endif]-->		
	</head>

  	<body class=\"body\" onload=\"login.value='';senha.value=''\">
		<div class=\"posicao2\" id=\"login\">
			<p><b><font face=\"Arial\" size=\"4\" color=\"navy\" >
			<center>LIBERA USUÁRIO</font></b></center> 
				<p>Sr. Operador favor inserir no campo Usuário o login de quem deseja desbloquear.
			<form action=\"aut.php\" method=\"post\" target=\"_self\" onsubmit=\"enviar.disabled=true\">
				<div class=\"divlogin\">
					<div class=\"tblogin\">
						<div class=\"div-select\">
							<select name=\"sistema\">
								<option disabled>Selecione o Sistema</option>
								<option value=\"ORACLE\" selected>ORACLE</option>
								<option value=\"ECOMEX\">ECOMEX</option>
							</select> 
							
						</div>
					</div>			
					<div class=\"tblogin\">
						<img class=\"imagem\" src=\"image/login.png\"><input placeholder=\"Usuário\" class=\"campo\" type=\"text\" name=\"login\" size=\"20\" id=\"pname\"/>
					</div>
					<div>
						<input type=\"submit\" class=\"btnColor\" name=\"enviar\" onclick=\"javascript:document.getElementById('blanket').style.display = 'block';document.getElementById('aguarde').style.display = 'block';\" value=\"Liberar\"/>
					</div>
				</div>
			</form>
		</div>     
		<center>
			<div id=\"blanket\"></div>
			<div id=\"aguarde\"></div>
		</center>
	</body>
</html>";
 }
  
  
?>

</font>
