<font face = "Arial" size = 3 >
<?php
# Autor: Rafael Fernando Nazatto

session_start();
if ($_SESSION['login'] != ""){
	if($_GET['acao'] == "exit"){
		session_destroy();
		header('Location: index.php');
	} else {
		header('Location: formulario.php');
	}
}else{

	if ($_GET['acao'] == "login"){
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		header('Last Modified: '. gmdate('D, d M Y H:i:s') .' GMT');
		header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
		header('Pragma: no-cache');
		header("Cache: no-cache");
		header('Expires: 0');

		$LDAPUser = $_POST["login"];
		$LDAPUserPassword = $_POST["senha"];
		$search_submitted = $LDAPUser;
		$ambiente = $_POST["sistema"];
		  
		if ($LDAPUser == "" || $LDAPUserPassword == ""){
			$texto = "<p>91 - Favor inserir seu login e senha antes de clicar em liberar!
					 <p><font color=\"red\"><b>O login e senha devem ser os mesmos usados para logar no Windows.</b></font>";
		} 
		  
		else { 
			  
			$SearchFor = $search_submitted;
			$SearchField="samaccountname";

			$LDAPHost = "dc.romi.com";
			$dn = "DC=romi,DC=com";
			$LDAPUserDomain = "@romi.com";
					
			$LDAPFieldsToFind = array("cn", "givenname", "samaccountname", "homedirectory", "telephonenumber", "mail");

			$cnx = ldap_connect($LDAPHost) or die($texto = "666-Urgente! Contate o SCOP atarvés dos ramais 9960/9961");
																	
			ldap_set_option($cnx, LDAP_OPT_PROTOCOL_VERSION, 3);
			ldap_set_option($cnx, LDAP_OPT_REFERRALS, 0);
				 
			ldap_bind($cnx,$LDAPUser.$LDAPUserDomain,$LDAPUserPassword) or die(header('Location: index.php?error=invalido'));
			error_reporting (E_ALL ^ E_NOTICE);
				  
			$filter="($SearchField=$SearchFor)";
			$sr=ldap_search($cnx, $dn, $filter);
			$info = ldap_get_entries($cnx, $sr);

			$x = $info["count"];

			if($x == 1){
				$nam=$info[0]['cn'][0];
				$sam=$info[0]['samaccountname'][0];
								
				foreach($info[0]['memberof'] as $grps){ 
					$gruposs = explode(" CN=", $grps);
					foreach($gruposs as $verfgrp){
							
						if ($verfgrp <> "") {
								
							if  ($verfgrp == "CN=Temp_scop,OU=SCOP,OU=DSIS,OU=DAF,OU=PD,OU=Departments,DC=romi,DC=com"){
								session_start();
									
								$_SESSION['login'] = $LDAPUser; 
								$_SESSION['nome'] = $nam;
								$_SESSION['login_time'] = date('m/d/Y H:i:s');
								
								$acessar = "true";
								break 2;
							} else {
								$acessar = "false";			
							} 
						}
					}
				}	
					
				if($acessar == "true"){
					header('Location: formulario.php');		
				} else {
					$texto = "Usuário não autorizado!";
				}
					
			}

			else {
				$texto = "<p>071-O Login $search_submitted não foi encontrado. Por favor tente novamente!
						<p><font color=\"red\"><b>O login e senha devem ser os mesmos usados para logar no Windows.</b></font>";
			}
		}
	}
	if ($_GET['error'] == "invalido"){
		$texto = "031-Login ou senha invalidos, Por favor tente novamente!";
	}
}
?>

<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
		<title>Envia E-mail SCOP</title>	
		<link href="css/bootstrap.min.css" rel="stylesheet">
		<!--[if IE]><link rel="stylesheet" type="text/css" href="css/ie.css"/><!-- <![endif]-->
		<!--[if !IE]>--><link rel="stylesheet" type="text/css" href="css/chrome.css"/><!-- <![endif]-->		
	</head>
	<body class="body">
		<div class="posicao2" id="login">
			<p><b><font face="Arial" size="4" color="navy" >
			<center>Envia E-mail SCOP</font></b><br>
				<p><?php echo $texto; ?>
			</center>
				<form action="index.php?acao=login" method="post">
					<div class="divlogin">			
						<div class="tblogin" style="display:table;">
							<span class="glyphicon glyphicon-user" style="font-size:25px; display:table-cell; vertical-align:middle;"></span>
							<input placeholder="Usuário" class="form-control form-espacamento" type="text" name="login" size="20" id="pname"/>
						</div>
						<div class="tblogin" style="display:table; padding-top:10px;">
							<span class="glyphicon glyphicon-lock" style="font-size:25px; display:table-cell; vertical-align:middle;"></span>
							<input placeholder="Senha" class="form-control form-espacamento" type="password" name="senha" size="20" />
						</div>
						<div>
							<br>
							<center><input type="submit" class="btn btn-default btn-tamanho" name="enviar" value="Login"/></center>
						</div>
					</div>
				</form>
		</div>     
	</body>
</html>
