<font face = "Arial" size = 3 >
<?php

  ldap_unbind($cnx);

  $arquivo = "contador.txt";

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

  $LDAPUser = $_POST["name_submitted"];
  $LDAPUserPassword = $_POST["passwd_submitted"];
  $search_submitted = $LDAPUser;
  $ambiente = $_POST["sistema"];

  $SearchFor = $search_submitted;
  $SearchField="samaccountname";

	$LDAPHost = "dc.romi.com";
	$dn = "DC=romi,DC=com";
	$LDAPUserDomain = "@romi.com";

  $LDAPFieldsToFind = array("cn", "givenname", "samaccountname", "homedirectory", "telephonenumber", "mail");

  $cnx = ldap_connect($LDAPHost) or die("011-Não foi possível conectar. Verifique seu usuário & senha, atualize a página e tente novamente. Caso o problema persista, contate o SCOP atarvés dos ramais 9960/9961");
  
  ldap_set_option($cnx, LDAP_OPT_PROTOCOL_VERSION, 3);
  ldap_set_option($cnx, LDAP_OPT_REFERRALS, 0);
  ldap_bind($cnx,$LDAPUser.$LDAPUserDomain,$LDAPUserPassword) or die("031-Não foi possível conectar. Verifique seu usuário & senha, atualize a página e tente novamente. Caso o problema persista, contate o SCOP atarvés dos ramais 9960/9961");
  
  error_reporting (E_ALL ^ E_NOTICE);
  
  $filter="($SearchField=$SearchFor)";
  $sr=ldap_search($cnx, $dn, $filter);
  $info = ldap_get_entries($cnx, $sr);

  $x = $info["count"];

  if($x == 1){
	$nam=$info[0]['cn'][0];
	$sam=$info[0]['samaccountname'][0];
	
	//print "Estamos trabalhando... <br>$nam. Por favor aguarde... <br><br>\n";
	echo shell_exec("sudo -u root sh ./xx_user_freedom.sh $sam $ipaddress $ambiente $contador");
  }

  else {
	print "051-Login $search_submitted não encontrado. Por favor tente novamente!<br>\n"; 
  }

  print "<br><br>Você é o visitante numero: ".$contador;

  fseek($handle, 0);

  fwrite($handle, $contador);

  fclose($handle);

?>

</font>
