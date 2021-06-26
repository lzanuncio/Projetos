<?php
session_start();
if ($_SESSION['login'] == ""){
	header('Location: index.php');
}
// COLOCA \ EM CARACTERES ESPECIAIS
//+------------------------------------------------+
//|    echo addslashes("zan \ \dc\netlogon  ");    |
//+------------------------------------------------+
include 'conexao.php';

//Coleta informações de Data e Hora
$log_date = date('d-m-Y');
$log_time = date('H:i:s');

//Expira sessão após 8h
if (date('m/d/Y H:i:s') > date('m/d/Y H:i:s', strtotime('+9 hour', strtotime($_SESSION['login_time'])))){
	session_destroy();
	header('Location: index.php');
}

//Insere no campo Saudação o horario automaticamente
if ($log_time < "12:00:00"){
	$autocomprimento = "Bom dia";
} else if ($log_time >= "12:00:00" && $log_time < "18:00:00") {
	$autocomprimento = "Boa tarde";
} else {
	$autocomprimento = "Boa noite";
}

$validaarquivo = true;
//Verifica se o NORMAS existe
if (isset($_REQUEST['submeter'])){
	//Verifica se o arquivo NORMAS existe
		include 'mail.php';
		$saudacao = $_POST["saudacao"];
		if ($saudacao == ""){$saudacao = "Prezado";};
		$login = $_POST["login"];
		$senha = $_POST["senha"];
		$nome_usuario = $_POST["nome_usuario"];
		$generic = $_POST["generic"];
		$para = $_POST["para"];
		$cc = $_POST["cc"];
		$assuntoemail = $_POST["chamado"];
		$id_corpo = $_POST["tipochamado"];
		$operador = $_POST["operador"];
		$cco = $_POST["cco"];

		$array_corpo = explode (",", $id_corpo);
		$corpo = $array_corpo[1];

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

		//Verifica qual botão foi apertado
		function get_post_action($name)
		{
			$params = func_get_args();

			foreach ($params as $name) {
				if (isset($_POST[$name])) {
					return $name;
				}
			}
		}
		
		switch(get_post_action('submit','visualizar')){
			case 'submit' : $acao = "enviar"; break;
			case 'visualizar': $acao = "visualizar";
		}
		//Preenche o corpo do e-mail
		$textmail = "<p>".$saudacao.",";

			$sql = mysql_query("select corpo, anexo, assunto from scop_envia_email where id = $corpo");
									while($exibe = mysql_fetch_assoc($sql)){
										$textmail .= $exibe['corpo'];
										$arquivo_anexo = $exibe['anexo'];
										$assunto_email = $exibe['assunto'];
									}
		$textmail = str_replace( '$login$', $login, $textmail );
		$textmail = str_replace( '$senha$', $senha, $textmail );
		$textmail = str_replace( '$nome_usuario$', $nome_usuario, $textmail );
		$textmail = str_replace( '$generic$', $generic, $textmail );
		
		$sql = mysql_query("select corpo from scop_envia_email where id = '0'");
		$dados = mysql_fetch_array($sql);

		$textmail .= $dados['corpo'];
		$textmail = str_replace('$operador$', $_POST['operador'],$textmail);

		$textmail .= "<font color=\"white\">msg-code 9417".$corpo;
		//------------------------------------------------------------------------------------------
		//Converte os anexos de Strig para Array
		$arq_anexo = explode (",", $arquivo_anexo);
		
		//verifica se o anexo existe, somente se o chamado tiver anexo
		if(!file_exists("../docs/Manuais_Procedimentos/Operacional/anexos/$arq_anexo[0].pdf") && $arquivo_anexo != ""){
			$validaarquivo = false;
		}
		
		if(!$validaarquivo){
			echo "<script>alert('Verificar o mapeamendo do servidor Omega com a pasta do SCOP, ou verificar se o arquivo $arq_anexo[0].pdf existe, E-mail não enviado!');</script>";
		} else {
			if ($acao == "enviar"){
				//Gera o log
				$fp = fopen("log/log_envio.html", "a");
				$log_envio = fwrite($fp, "<tr><td>".$ipaddress."</td><td>".$log_date."</td><td>".$log_time."</td><td>".$assunto_email."</td><td>".$_SESSION['login']."</td><td>".$para."</td><td>".$cc."</td><td>".$cco."</td></tr>\n");
				fclose($fp);
				
				//Chama a função no mail.php para envio de e-mail
				envia_email($para, $cc, $cco, $assuntoemail, $textmail, $arq_anexo);
			} 
		}
	}

?>
<html lang="pt-br">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
		<title>Envia E-mail SCOP</title>	
		<link href="css/bootstrap.min.css" rel="stylesheet">
		<script src="js/jquery.js"></script>
		<script src="js/bootstrap.min.js"></script>	
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.12.4/css/bootstrap-select.min.css">		
		<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.12.4/js/bootstrap-select.min.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.12.4/js/i18n/defaults-*.min.js"></script>
		<link rel="stylesheet" type="text/css" href="css/chrome2.css"/><!-- <![endif]-->
		
		<script type="text/javascript" src="js/formulario.js"></script>
		<script>
			function submete(botaoap){
				var confere = document.getElementById("options").value;	
				
				if (confere == "inativo"){
					alert('Por favor selecione algum e-mail!');
					return false;
				} else {
					if (botaoap != "btn-visu"){
						var r = confirm('Você realmente deseja enviar este e-mail?');
						return r;
					}
				}	
			}
			function confirmacao(){
			var r = confirm('Você realmente deseja fazer logoff?');
			if (r == false){
				event.preventDefault();
			}
		}
		</script>	


	</head>
	<body>
	<div class="popup-bg" style="display: none"></div>

		
			<div class="posicao">
			
				<a class="sair" onclick="confirmacao()" href="index.php?acao=exit"><span style="font-size: 20px; color: red;" class="glyphicon glyphicon-remove"</span></a>
				<p><b>
				<center class="titulo">ENVIA E-MAIL</b></center>
				<form id="form" action="formulario.php?submeter" method="post" onsubmit="return submete(this.submited);">
					<div class="div-select">
						<center>
							<select id="options" name="tipochamado" onchange="optionCheck()" class="selectpicker" data-live-search="true">
								<option value="inativo" disabled selected>Selecione o chamado</option>";
									<?php
									$sql = mysql_query("select id, assunto, opcao, generic, status from scop_envia_email order by assunto ASC");
									while($exibe = mysql_fetch_assoc($sql)){
										if ($exibe['id'] != "0" && $exibe['status'] == "ativo"){
										echo "<option data-tokens=\"".$exibe['assunto']."\" value=\"".$exibe['generic'].",".$exibe['id'].",".$exibe['opcao']."\">".$exibe['assunto']."</option>";
										}
									}
									?>
							</select>	
						</center>	
					</div>
					<div  class="tblogin">
						<br><br>
						<div id="saudacao" style="visibility:visible;" target="padding">
							<input class="form-control form-espacamento" id="id_saudacao" name="saudacao" type="search" placeholder="Saudação" value="<?php echo $autocomprimento; ?>" autocomplete="off"><br>
						</div>
						
						<div id="para" style="visibility:visible;" target="padding">
							<input class="form-control form-espacamento" id="id_para" name="para" type="text" placeholder="Para" autocomplete="off" required>
						</div>	
						
						<div id="cc" style="visibility:visible;" target="padding">
							<input class="form-control form-espacamento" id="id_cc" name="cc" type="text" placeholder="CC" autocomplete="off"><br>
						</div>	
						
						<div id="cco" style="visibility:visible;" target="padding">
							<input class="form-control form-espacamento" id="id_cco" name="cco" type="text" placeholder="CCO" autocomplete="off"><br>
						</div>
						
						<div id="chamado" style="visibility:visible;" target="padding">
							<input class="form-control form-espacamento" id="id_chamado" name="chamado" type="text" placeholder="Assunto" autocomplete="off" required><br>
						</div>
									
						<div id="nome_usuario" style="visibility:hidden;" target="padding">
							<input class="form-control form-espacamento" id="id_nome_usuario" name="nome_usuario" type="text" placeholder="Usuário" autocomplete="off"><br>
						</div>
						
						<div id="login" style="visibility:hidden;" target="padding">
							<input class="form-control form-espacamento" id="id_login" name="login" type="text" placeholder="Login" autocomplete="off"><br>
						</div>	
						
						<div id="senha" style="visibility:hidden;" target="padding">
							<input class="form-control form-espacamento" id="id_senha" name="senha" type="text" placeholder="Senha" autocomplete="off"><br>
						</div>	
						
						<div id="generic" style="visibility:hidden;" target="padding">
							<input class="form-control form-espacamento" id="id_generic" name="generic" type="text" placeholder="" autocomplete="off"><br>
						</div>	
					</div>
					<input id="operador" name="operador" type="hidden" value="<?php echo $_SESSION['nome'];?>">
					<center>
						<table border="0">
							<tr>
								<td>
									<input id="Enviar" name="submit" type="submit" value="Enviar" class="btn btn-default btn-tamanho" onclick="this.form.submited=this.value;">
								</td>
								<td>
									<input class="btn btn-default btn-tamanho" id='btn-visu' type="button" value="visualizar" onclick="this.form.submited=this.value;"/>
								</td>
								<td>
									<a href="cadastrados.php" class="btn btn-default btn-tamanho">Alterar</a>
								</td>
							</tr>
						</table>
					</center>
				</form>
				
				
			</div>
			
			
			<div class="popup" style="display: none"></div>
			<script>
	
	 var form = document.querySelector('#form');
	 
	 var visu_option = document.querySelector('#options');
	 var visu_saudacao = document.querySelector('#id_saudacao');
	 var visu_para = document.querySelector('#id_para');
	 var visu_cc = document.querySelector('#id_cc');
	 var visu_cco = document.querySelector('#id_cco');
	 var visu_chamado = document.querySelector('#id_chamado');
	 var visu_nome_usuario = document.querySelector('#id_nome_usuario');
	 var visu_login = document.querySelector('#id_login');
	 var visu_senha = document.querySelector('#id_senha');
	 var visu_generic = document.querySelector('#id_generic');
	 var visu_operador = document.querySelector('#operador');

	 var popupBg = document.querySelector('.popup-bg');	 
	 var popup = document.querySelector('.popup');
	 var btnVisualizar = document.querySelector('#btn-visu');
	 
	 	 
	popupBg.onclick = escondePopup;
	btnVisualizar.onclick = onVisualizar;
	
	 
	 function onVisualizar(ev) {
  // url do arquivo que envia o conteúdo do popup
  var url = 'pop.php';

  // os parâmetros que devem ser enviados por POST, no formato:
  // chave=valor&chave2=valor2...
  var params = 'option=' + visu_option.value + '&saudacao=' + visu_saudacao.value + '&para=' + visu_para.value + '&cc=' + visu_cc.value + '&cco=' + visu_cco.value + '&chamado=' + visu_chamado.value + '&nome_usuario=' + visu_nome_usuario.value + '&login=' + visu_login.value + '&senha=' + visu_senha.value + '&generic=' + visu_generic.value + '&operador=' + visu_operador.value; 

  var xhr = new XMLHttpRequest();
  xhr.open('POST', url);
  xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
  xhr.onreadystatechange = function() {
    if (xhr.readyState == 4 && xhr.status == 200) {
    	// ação ao receber retorno do pedido
      popup.innerHTML = xhr.responseText;
      mostraPopup();
    }
  }
  xhr.send(params);
}
function mostraPopup() {
	popup.style.display = 'block';
  popupBg.style.display = 'block';
}

function escondePopup() {
  popup.style.display = 'none';
  popupBg.style.display = 'none';
}
     </script>
	</body>
</html>