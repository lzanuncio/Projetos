<?php
# Autor: Rafael Fernando Nazatto
session_start();

//Verifica se esta logado
if ($_SESSION['login'] == ""){
	header('Location: index.php');
}

//Expira sessão após 8h
if (date('m/d/Y H:i:s') > date('m/d/Y H:i:s', strtotime('+9 hour', strtotime($_SESSION['login_time'])))){
	session_destroy();
	header('Location: index.php');
}

include 'conexao.php';
$filtro = "ativo";
//Exclui a ocorrencia selecionada
if ($_GET['acao'] == "excluir"){
	$id_dele = $_GET['id'];
	$result = mysql_query("delete from scop_envia_email where id = '$id_dele'");
	header('Location: cadastrados2.php');
}

if ($_GET['acao'] == "ativa"){
	$id_alt = $_GET['id'];
	$result = mysql_query("update scop_envia_email set status = 'ativo' WHERE id = $id_alt");
	header('Location: cadastrados2.php');
}

if ($_GET['acao'] == "inativa"){
	$id_alt = $_GET['id'];
	$result = mysql_query("update scop_envia_email set status = 'inativado' WHERE id = $id_alt");
	header('Location: cadastrados2.php');
}

if (isset($_REQUEST['filtro'])){
	$filtro = $_POST['select_filtro'];
}

if (isset($_REQUEST['alterastatus'])){
	$input_filtro = $_POST['input_filtro'];
	$id_input = $_POST['input_filtro2'];
	$acao = $_POST['acao_input'];
	
	if($acao == "ativa"){
		$result = mysql_query("update scop_envia_email set status = 'ativo' WHERE id = $id_input");
	}
	if($acao == "inativa"){
		$result = mysql_query("update scop_envia_email set status = 'inativado' WHERE id = $id_input");
	}
	 $filtro = $input_filtro;
	
}
?>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
		<title>Envia E-mail SCOP</title>	
		<link href="css/bootstrap.min.css" rel="stylesheet">		
		<link rel="stylesheet" type="text/css" href="css/chrome2.css"/><!-- <![endif]-->
		<script>
		//Confirmação de exclusão
		function verf(chamado){
			var r = confirm('Deseja excluir o chamado: ' + chamado + '?');
			if (r == false){
				event.preventDefault();
			}
		}	

		function submetefiltro(){
			document.getElementById('theForm').submit();			
		}
		
		function atualizastatus(id, acao){			
			document.getElementById('input_filtro2').value = id;
			document.getElementById('acao_input').value = acao;
			document.getElementById('theForm2').submit();
		}
		</script>
	</head>

	<body class="body">
		<script src="js/jquery.js"></script>
     <script src="js/bootstrap.min.js"></script>
	<div class="popup-bg" style="display: none"></div>
		<div class="posicao4" id="login">
			<p><b>
			<center class="titulo">E-MAILS CADASTRADOS</font></b></center>

<form id="theForm" action="cadastrados2.php?filtro" method="POST">		
	<select name="select_filtro" onchange="submetefiltro()">
		<option value="todos" <?php if($filtro == "todos"){echo "selected";}?>>Todos</option>
		<option value="ativo" <?php if($filtro == "ativo"){echo "selected";}?>>Ativos</option>
		<option value="inativado" <?php if($filtro == "inativado"){echo "selected";}?>>Inativos</option>
	</select>
</form>

<form id="theForm2" action="cadastrados2.php?alterastatus" method="POST">
	<input id="input_filtro" name="input_filtro" type="hidden" value="<?php echo $filtro;?>">
	<input id="input_filtro2" name="input_filtro2" type="hidden" value="">
	<input id="acao_input" name="acao_input" type="hidden" value="">
</form>
			
			
			<table class="table table-striped table-bordered table-hover table-custom">
					<tr>
						<th colspan="1">Chamado</th>
						<th colspan="2">Acões</th>
					</tr>
					<?php
					$pgn = $_GET['pgn'];

					$quantidade = 20;
					if($pgn == "" || $pgn == null || $pgn == 0){
						$inicio = 0;
					} else {
						$inicio = ($pgn * $quantidade) - $quantidade;
					}
					//Faz select de todos as ocorrencias e as exibe como tabela
					$sql = mysql_query("select id, assunto, status from scop_envia_email order by assunto ASC LIMIT $inicio, $quantidade");
					$sql2 = mysql_query("select id from scop_envia_email");
					$maxproximo = ceil(mysql_num_rows($sql2) / $quantidade);

						while($exibe = mysql_fetch_assoc($sql)){
							
							if($filtro == "todos" || $filtro == ""){							
								echo "<tr>";
									echo "<td>".$exibe['assunto']."</td>";
									echo "<td><center><a class=\"editar\" href=\"cadastra.php?acao=edita&id=".$exibe["id"]."\"><span class=\"glyphicon glyphicon-pencil\" style=\"color: black; padding-top:3px;\"></span></a></center></td>";
									if ($exibe['status'] == "ativo"){
										echo "<td><center><a style=\"color: green; padding-top: 2px;\" class=\"glyphicon glyphicon-off\" href=\"#\" onclick=\"atualizastatus(".$exibe["id"].", 'inativa')\"></a></center></td>";
									} else {
										echo "<td><center><a style=\"color: red; padding-top: 2px;\" class=\"glyphicon glyphicon-off\" href=\"#\" onclick=\"atualizastatus(".$exibe["id"].", 'ativa')\"></a></center></td>";
									}
									//echo "<td><center><a class=\"excluir\" href=\"cadastrados2.php?acao=excluir&id=".$exibe["id"]."\" onclick=\"verf('".$exibe['assunto']."')\"><span class=\"glyphicon glyphicon-trash\" style=\"color: black; padding-top:3px;\"></span></a></center></td>";
								echo "</tr>";
							} else {
								if($filtro == $exibe['status']){
										echo "<tr>";
											echo "<td>".$exibe['assunto']."</td>";
											echo "<td><center><a class=\"editar\" href=\"cadastra.php?acao=edita&id=".$exibe["id"]."\"><span class=\"glyphicon glyphicon-pencil\" style=\"color: black; padding-top:3px;\"></span></a></center></td>";
											if ($exibe['status'] == "ativo"){
												echo "<td><center><a style=\"color: green; padding-top: 2px;\" class=\"glyphicon glyphicon-off\" href=\"#\" onclick=\"atualizastatus(".$exibe["id"].", 'inativa')\"></a></center></td>";
											} else {
												echo "<td><center><a style=\"color: red; padding-top: 2px;\" class=\"glyphicon glyphicon-off\" href=\"#\" onclick=\"atualizastatus(".$exibe["id"].", 'ativa')\"></a></center></td>";
											}
											//echo "<td><center><a class=\"excluir\" href=\"cadastrados2.php?acao=excluir&id=".$exibe["id"]."\" onclick=\"verf('".$exibe['assunto']."')\"><span class=\"glyphicon glyphicon-trash\" style=\"color: black; padding-top:3px;\"></span></a></center></td>";
										echo "</tr>";
								}
							}
						}
					?>					
					<tr>
						<td colspan="1"><center><b>Inserir novo chamado</b></center></td>
						<td colspan="2"><center><a href="cadastra.php"><span class="glyphicon glyphicon-plus" style="color: black; padding-top:3px;"></span></a></center></td>
			</table><br>
			<center>
					<button class="btn btn-default" type="button" onclick="anterior()" <?php if($pgn == "" || $pgn == null || $pgn == 0 || $pgn == 1){echo "disabled";}?>>Anterior</button>
					<button class="btn btn-default" type="button" onclick="proximo()" <?php if($pgn == $maxproximo){echo "disabled";}?>>Proximo</button>			
					<script>
						function anterior(){
							window.location.href = "cadastrados2.php?pgn=<?php echo --$pgn;?>";
						}
						
						function proximo(){
							window.location.href = "cadastrados2.php?pgn=<?php echo $pgn += 2;?>";
						}
					</script>
				<p><a href="formulario.php" class="btn btn-default btn-tamanho">Voltar</a>
				<a id="btn-log" href="#" class="btn btn-default btn-tamanho btn-some">Log</a>
			</center>
			
		</div>  
<div class="popup" style="display: none"></div>
							<script>
	 var popupBg = document.querySelector('.popup-bg');	 
	 var popup = document.querySelector('.popup');
	 var btnVisualizar = document.querySelector('#btn-log');
	 	 
	popupBg.onclick = escondePopup;
	btnVisualizar.onclick = onVisualizar;
	 
	 function onVisualizar(ev) {
  // url do arquivo que envia o conteúdo do popup
  var url = 'log/log_envio.html';

  // os parâmetros que devem ser enviados por POST, no formato:
  // chave=valor&chave2=valor2...
  var params = ''; 

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