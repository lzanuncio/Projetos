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

  $cnx = ldap_connect($LDAPHost) or die(chama_tela("011-Urgente! Contate o SCOP atarvés dos ramais 9960/9961</p>"));
													
  ldap_set_option($cnx, LDAP_OPT_PROTOCOL_VERSION, 3);
  ldap_set_option($cnx, LDAP_OPT_REFERRALS, 0);
 
  ldap_bind($cnx,$LDAPUser.$LDAPUserDomain,$LDAPUserPassword) or die(chama_tela("031-Login ou senha invalidos, Por favor tente novamente!</p>
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
	$locked=$info[0]['badpwdcount'][0];

	if($locked > 2){
		chama_tela("051-Usuário Bloqueado. Contate o SCOP atarvés dos ramais 9960/9961");
	}
	else{ 
	
		//print "Estamos trabalhando... <br>$nam. Por favor aguarde... <br><br>\n";
		shell_exec("sudo -u root sh ./xx_user_freedom.sh $sam $ipaddress $ambiente $contador");
	
		if ($ambiente == "ORACLE"){		
			fseek($handle, 0);
			fwrite($handle, $contador);
			fclose($handle);
		}

		chama_tela("<p>Usuário desbloqueado com sucesso!
					<p>Você é o visitante numero: $contador");
	}
  }

  else {
	chama_tela("<p>071-O Login $search_submitted não foi encontrado. Por favor tente novamente!</p>
				<p><font color=\"red\"><b>O login e senha devem ser os mesmos usados para logar no Windows.</b></font>");
  }
  }
 function chama_tela($texto){
	echo "
		<html>
			<head>
				<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"/>
				<title>Auto-Desbloqueio</title>		
				<!--[if IE]><link rel=\"stylesheet\" type=\"text/css\" href=\"css/ie.css\"/><!-- <![endif]-->
				<!--[if !IE]>--><link rel=\"stylesheet\" type=\"text/css\" href=\"css/chrome.css\"/><!-- <![endif]-->
			</head>
			<body class=\"body\" onload=\"login.value='';senha.value=''\">

				<div class=\"posicao\" id=\"login\">
					<p><b><font face=\"Arial\" size=\"4\" color=\"navy\" >
					<center>LIBERA USUÁRIO</font></p></b> 
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
  
  
?>

</font>
