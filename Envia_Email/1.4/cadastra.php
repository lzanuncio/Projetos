<?php
# Autor: Rafael Fernando Nazatto

//Verifica se esta logado, se não redirenciona para a tela de login
session_start();
if ($_SESSION['login'] == ""){
	header('Location: index.php');
}

//Expira sessão após 8h
if (date('m/d/Y H:i:s') > date('m/d/Y H:i:s', strtotime('+9 hour', strtotime($_SESSION['login_time'])))){
	session_destroy();
	header('Location: index.php');
}

//Inclui a conexao com o BD
include 'conexao.php';

//VARIAVEIS PARA CRIAÇÃO DE E-MAIL
$titulo_html = "CADASTRA NOVO E-MAIL";
$form_action = "cadastra.php?insere";
$nome_botao = "Cadastrar";

//Verifica proximo ID
$show_auto_increment = "SHOW TABLE STATUS LIKE 'scop_envia_email'";
$result_auto_increment = mysql_query($show_auto_increment);

$array_auto_increment = mysql_fetch_array($result_auto_increment);
$echo_auto_increment = $array_auto_increment['Auto_increment'];

if (isset($_REQUEST['altera'])){
	//VARIAVEIS PARA EDIÇÃO DE E-MAIL
	$assunto = addslashes($_POST['chamado']);
	$corpo = addslashes($_POST['corpo']);
	$anexo = "";
	$id = $_POST['input_id'];
	$generic = addslashes($_POST['generic']);
	
	//Verifica se o foi colocado anexo
	if (isset($_POST['anexo'])){
		//Se tem anexo, transforma o array em string
		foreach ($_POST['anexo'] as $anexoind){
			$anexo .= $anexoind.", ";
		}
		$size = strlen($anexo);
		$anexo = substr($anexo,0, $size-2);
	}
	//Faz os updates
	$result = mysql_query("update scop_envia_email set assunto = '$assunto' WHERE id = $id");
	$result = mysql_query("update scop_envia_email set corpo = '$corpo' WHERE id = $id");
	$result = mysql_query("update scop_envia_email set anexo = '$anexo' WHERE id = '$id'");
	$result = mysql_query("update scop_envia_email set generic = '$generic' WHERE id = '$id'");

	$itens = "";
	$termo = array('\$nome_usuario\$','\$login\$','\$senha\$');
	$array_tamanho = count($termo);
	for($i = 0; $i < $array_tamanho; $i++){
		$pattern = '/' . $termo[$i] . '/';//Padrão a ser encontrado na string $tags
		if (preg_match($pattern, $corpo)) {
			if($termo[$i] == $termo[0]){
				$itens .= "a1,";
			} else if($termo[$i] == $termo[1]){
				$itens .= "b1,";
			} else if($termo[$i] == $termo[2]){
				$itens .= "c1,";
			}

			
		}
	}
	
	if ($_POST['d1'] == "d1"){
		$itens .= "d1";
	} else {
		$size = strlen($itens);
		$itens = substr($itens,0, $size-1);
	}
	$result = mysql_query("update scop_envia_email set opcao = '$itens' WHERE id = $id");
	header('Location: cadastrados.php');
}
//INSERE AS INFORMAÇÕES DO BANCO
if (isset($_REQUEST['insere'])){
	//Verifica se o foi colocado anexo
	if (isset($_POST['anexo'])){
	//Se tem anexo, transforma o array em string
	foreach ($_POST['anexo'] as $anexoind){
		$anexo .= $anexoind.", ";
	}
	
	$size = strlen($anexo);
	$anexo = substr($anexo,0, $size-2);
	}
	
	$assunto = addslashes($_POST['chamado']);
	$corpo = addslashes($_POST['corpo']);
	$anexo = $anexo;
	$generic = addslashes($_POST['generic']);
	
	$itens = "";
	$termo = array('\$nome_usuario\$','\$login\$','\$senha\$');
	$array_tamanho = count($termo);
	for($i = 0; $i < $array_tamanho; $i++){
		$pattern = '/' . $termo[$i] . '/';//Padrão a ser encontrado na string $tags
		if (preg_match($pattern, $corpo)) {
			if($termo[$i] == $termo[0]){
				$itens .= "a1,";
			} else if($termo[$i] == $termo[1]){
				$itens .= "b1,";
			} else if($termo[$i] == $termo[2]){
				$itens .= "c1,";
			}

			
		}
	}
	if ($_POST['d1'] == "d1"){
		$itens .= "d1";
	} else {
		$size = strlen($itens);
		$itens = substr($itens,0, $size-1);
	}
	
	$result = mysql_query("INSERT INTO scop_envia_email (assunto, corpo, anexo, opcao, generic) values ('$assunto', '$corpo', '$anexo', '$itens', '$generic')");
	header('Location: cadastrados.php');
}

//Prepara a tela para a edição, busca os campos ja preenchidos no banco
if ($_GET['acao'] == "edita"){
	$id_edit = $_GET['id'];
	$titulo_html = "ALTERAÇÃO E-MAIL";
	$form_action = "cadastra.php?altera";
	$nome_botao = "Salvar";
	
	$sql = mysql_query("select * from scop_envia_email where id = $id_edit");
	$dados = mysql_fetch_array($sql);
	$array_opcoes = explode (",", $dados['opcao']);
}
?>
<html>
		<head>
			<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
			<link href="css/bootstrap.min.css" rel="stylesheet">
			<link rel="stylesheet" href="css/editorwys.css" type="text/css" media="all" />
			<script type="text/javascript" src="ckeditor/ckeditor.js"></script>
			<script>
				function teste(){
				document.getElementById('generic').onchange = function() {
					document.getElementById('generict').disabled = !this.checked;
					document.getElementById('generict').value='';
				};
				}
			</script>
			<title>Envia E-mail SCOP</title>		
			<script src="js/jquery.js"></script>
		<script src="js/bootstrap.min.js"></script>	
			<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.12.4/css/bootstrap-select.min.css">		
		<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.12.4/js/bootstrap-select.min.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.12.4/js/i18n/defaults-*.min.js"></script>
			<link rel="stylesheet" type="text/css" href="css/chrome2.css"/><!-- <![endif]-->
		</head>
		<body class="body">
			<div id="dvCenterEditor"style="width:400px;">
			
		</div>
			<div class="posicao4" id="login">
				<p><b>
				<center class="titulo"><?php echo $titulo_html;?></font></b></center>
					<div class="divlogin">	
						<form action="<?php echo $form_action; ?>" method="post">
						
							<p><label for="input_id">ID</label><br>
							<input id="input_id" class="form-control" type="text" name="input_id" value="
<?php 
							
							if ($nome_botao == "Salvar") { 
								echo $dados['id'];
							} else if ($nome_botao == "Cadastrar") { 
								echo $echo_auto_increment;
							} else { 
								echo "ID não pôde ser exibido!";
							} 
							
?>" readonly/>						
							<p><label for="chamado">Ocorrência</label><br>
							<input id="chamado" onClick="oi()" class="form-control" type="text" name="chamado" value="<?php echo $dados['assunto'];?>"/>
						
						
<script>
	var element = CKEDITOR.document.getById( 'txtEditor' );
element.on( 'click', function( ev ) {
        someFunction();
    });
	
	function someFunction(){
		alert('09');
	}
</script>							
						
						
							<p><label for="txtEditor">Corpo</label><br>
							<textarea class="ckeditor"  id="txtEditor"  name="corpo"><?php echo $dados['corpo']?></textarea>
							
							<div id="div_select">
								<b>Nome Anexo</b><br>
								<select id="selectmult" name="anexo[]" class="selectpicker form-control" multiple title="Escolha o(s) Anexo(s)">
									<?php
										$dir = "../docs/Manuais_Procedimentos/Operacional/anexos/";

										/* Abre o diretório */
										$pasta= opendir($dir);
										$string_arquivo = "";
										/* Loop para ler os arquivos do diretorio */
										while ($arquivo = readdir($pasta)){

											/* Verificacao para exibir apenas os arquivos e nao os caminhos para diretorios superiores */
											if ($arquivo != "." && $arquivo != ".."){
												$string_arquivo .= $arquivo;
											}	
										}
										$size = strlen($string_arquivo);
										$string_arquivo = substr($string_arquivo,0, $size-4);
										$array_arquivo = explode (".pdf", $string_arquivo);
										sort($array_arquivo);
										foreach ($array_arquivo as $exibe_arquivo){						
											if(isset($dados['anexo'])){
												$pattern = "/".$exibe_arquivo."/";
												if(preg_match($pattern, $dados['anexo'])){
													echo "<option value=\"".$exibe_arquivo."\" selected>".$exibe_arquivo."</option>";
												} else {
													echo "<option value=\"".$exibe_arquivo."\">".$exibe_arquivo."</option>";
												}	
											} else {
												echo "<option value=\"".$exibe_arquivo."\">".$exibe_arquivo."</option>";
											}
										}
										
									?>
								</select>
							</div>
							<br>
							<div class="row">
								<div class="col-md-9">
									<label for="generict">Nome Variavel Generica</label>
								</div>
								<div class="col-md-3">
									<input id="generic" id="generic" type="checkbox" name="d1" value="d1" onclick="teste()" <?php foreach($array_opcoes as $verificacao){if($verificacao == "d1"){echo "checked";}}?>>
									<label for="generic">$generic$</label>
								</div>
							</div>
							
							<input class="form-control" id="generict" type="text" name="generic" value="<?php foreach($array_opcoes as $verificacao){if($verificacao == "d1"){echo $dados['generic'];}}?>" <?php if(isset($array_opcoes)){foreach($array_opcoes as $verificacao){if($verificacao != "d1"){echo " disabled";}}}else{echo "disabled";}?>>
							<center>
								<table border="0">
									<tr>
										<td>
											<input type="submit" class="btn btn-default btn-tamanho" name="enviar" value="<?php echo $nome_botao;?>"/>
										</td>
										<td>
											<a href="cadastrados.php" class="btn btn-default btn-tamanho">Cancelar</a>
										</td>
								</table>
							</center>

					</div>
				</form>
			</div>     
			<center>
			</center>
		</body>
	</html>
