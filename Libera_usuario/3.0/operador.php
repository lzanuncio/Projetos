<?php
session_start();
if ($_SESSION['login'] == ""){
	header('Location: tst.php');
}
?>
<font face = "Arial" size = 3 >
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
			<p><b><font face="Arial" size="4" color="navy" >
			<center>LIBERA USUÁRIO</font></b> 
				<p>Insira o login o usuário
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
						<input placeholder="Usuário" class="campo" type="text" name="login" size="20" id="pname" required/>
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