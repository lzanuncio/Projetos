<?php
# Autor: Rafael Fernando Nazatto
session_start();

if ($_SESSION['login'] != ""){
	include 'conexao.php';

function envia_email($para, $cc, $cco, $assuntoemail, $corpo, $aqr_anexo){
	$boundary = "XYZ-".md5(date("dmYis"))."-ZYX";
	
	//echo somente para teste, ser?omentado ap?? termino.
	/*
	echo "Para: ".$para;
	echo "<br><br>CC:<br>".$cc;
	echo "<br><br>CCO:<br>".$cco;
	echo "<br><br>Assunto:<br>".$assuntoemail;
	echo "<br><br>Corpo:".$corpo;
	foreach($aqr_anexo as $teste){
		echo "<br><br>Anexo".$teste;
	}
		*/		
if (!strpbrk($para, '@')){
	$envia_para = explode (",", $para);
	foreach($envia_para as $from){
		$trim = trim ($from, " ");
		$trim .= "@romi.com, ";
		$vai .= $trim;
	}
} else { $vai = $para;}
	
	if ($cc != "" && !strpbrk($cc, '@')){
		$envia_cc = explode (",", $cc);
		foreach($envia_cc as $cop){
			$trim = trim ($cop, " ");
			$trim .= "@romi.com, ";
			$copia .= $trim;
		}
	} else { $copia = $cc; }
	
	if ($cco != "" && !strpbrk($cco, '@')){
		$envia_cco = explode (",", $cco);
		foreach($envia_cco as $coop){
			$trim = trim ($coop, " ");
			$trim .= "@romi.com, ";
			$copiao .= $trim;
		}
	} else { $copiao = $cco; }
	
	foreach($aqr_anexo as $veranexo){
		if ($veranexo == ""){
			$temanexo = "";
		} else {
			$temanexo = "anexo";
		}
	}

	if($temanexo == ""){
		//CABE?LHO NECESSARIO PARA O E-MAIL Nd CAIR COMO SPAM, SEM ANEXO
		$headers =  "Content-Type:text/html; charset='UTF-8'\n";
		$headers .= "From:  <scop@romi.com>\n"; //Vai ser //mostrado que  o email partiu deste email e seguido do nome
		$headers .= "X-Sender:  <scop@romi.com>\n"; //email do servidor //que enviou
		$headers .= "Cc: $copia \n"; 
		$headers .= "Bcc: $copiao scop@romi.com \n"; 
		$headers .= "X-Mailer: PHP  v".phpversion()."\n";
		$headers .= "X-IP:  ".$_SERVER['REMOTE_ADDR']."\n";
		$headers .= "Return-Path:  <scop@romi.com>\n"; //caso a msg //seja respondida vai para  este email.
		$headers .= "MIME-Version: 1.0\n";
		$mensagem = $corpo;
		
	} else {
//DIRETORIO ARQUIVO
$i = 0;
foreach($aqr_anexo as $arquivo_anexo){
	$trim = trim ($arquivo_anexo, " ");
	$path = "../docs/Manuais_Procedimentos/Operacional/anexos/$trim.pdf";//$aqr_anexo
	if (!file_exists($path)){
		header('Location: cadastra.php?erro=erro');		
	} else {
	$fileType[$i] = mime_content_type( $path );
	$fileName[$i] = basename( $path );
//Pegando o conte??do arquivo
	$fp = fopen( $path, "rb" ); // abre o arquivo enviado
	$anexo[$i] = fread( $fp, filesize( $path ) ); // calcula o tamanho
	$anexo[$i] = chunk_split(base64_encode( $anexo[$i] )); // codifica o anexo em base 64
	fclose( $fp ); // fecha o arquivo
	$i ++;

	//CABE?LHO NECESSARIO PARA O E-MAIL Nd CAIR COMO SPAM, ESSE TRECHO S??NECESSARIO CASO TENHA ANEXOS.
	$headers = "MIME-Version: 1.0" . PHP_EOL;
	$headers .= "Content-Type: multipart/mixed; ";
	$headers .= "boundary=" . $boundary . PHP_EOL;
	$headers .= "From:  <scop@romi.com>\n"; //Vai ser mostrado que  o email partiu deste email e seguido do nome
	$headers .= "Cc: $copia \n";
	$headers .= "Bcc: $copiao scop@romi.com \n"; 
	$headers .= "X-Sender:  <scop@romi.com>\n"; //email do servidor que enviou
	$headers .= "$boundary" . PHP_EOL;
	
//CORPO DO E-MAIL
			$i = 0;
	        $mensagem = "--$boundary\nContent-type: text/html\nContent-Transfer-Encoding: 7bit\n\n$corpo\n\n";
            foreach ($fileName as $nome_arquivo){
				$mensagem .= "--$boundary\nContent-type: $filetype[$i]\nContent-Disposition: attachment; filename=" . $nome_arquivo . "\nContent-Transfer-Encoding: base64\n\n$anexo[$i]\n";
            $i ++;
			
$mensagem .= "--$boundary\r--\n";}
}
	}
	}
//COMENTADO PARA Nd ENVIAR E-MAIL (NECESSARIO REMOVER O COMENTARIO)				
	mail($vai, $assuntoemail, $mensagem, $headers);  //fun? que faz o envio do email*/
	header('Location: formulario.php');
}
}else{
header('Location: index.php');
}
?>