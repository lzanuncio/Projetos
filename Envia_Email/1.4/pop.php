<?php
session_start();
if ($_SESSION['login'] == ""){
	header('Location: index.php');
}
?>

    <div class="modal-dialog">
         <div class="modal-content">
             <div class="modal-header">
                 <h4 class="modal-title">
				 <?php 
				 
				 include 'conexao.php';
						$id_edit = $_POST['option'];
						$array_corpo = explode (",", $id_edit);
						$scorpo = $array_corpo[1];
	$sql = mysql_query("select corpo, assunto, anexo from scop_envia_email where id = $scorpo");
	$dados = mysql_fetch_array($sql);
				 
				 echo $dados['assunto'];
				 
				 ?></h4>
            </div>
             <div class="modal-body">
			 
					<?php
							$anexo = $dados['anexo'];
							
							if (!strpbrk($_POST['para'], '@')){
								$envia_para = explode (",", $_POST['para']);
								foreach($envia_para as $from){
									$trim = trim ($from, " ");
									$trim .= "@romi.com, ";
									$vai .= $trim;
									$size = strlen($vai);
									$vai = substr($vai,0, $size-2);
								}
							} else {
								$vai = $_POST['para'];
							}
							
							if ($_POST['cc'] != ""){
								if(!strpbrk($_POST['cc'], '@')){
									$envia_cc = explode (",", $_POST['cc']);
									foreach($envia_cc as $cop){
										$trim = trim ($cop, " ");
										$trim .= "@romi.com, ";
										$copia .= $trim;
										$size = strlen($copia);
										$copia = substr($copia,0, $size-2);
									}
								} else { $copia = $_POST['cc']; }
							}
							
							if ($_POST['cco'] != ""){
								if(!strpbrk($_POST['cco'], '@')){
									$envia_cco = explode (",", $_POST['cco']);
									foreach($envia_cco as $coop){
										$trim = trim ($coop, " ");
										$trim .= "@romi.com, ";
										$copiao .= $trim;
										$size = strlen($copiao);
										$copiao = substr($copiao,0, $size-2);
									}
								} else { $copiao = $_POST['cco']; }
							}

						echo "Para: $vai";
						
						if($_POST['cc'] != ""){
							echo "<br><br>CC: $copia";
						}
						if($_POST['cco'] != ""){
							echo "<br><br>CCO: $copiao";
						}
						
						if($anexo != ""){
							echo "<br><br>Anexo: $anexo";
						}
						
						echo "<br><br>Assunto: ".$_POST['chamado'];
								
						$saudacao = $_POST["saudacao"];
						if ($saudacao == ""){$saudacao = "Prezado";};
						
						echo "<br><br><br>";
						$corpo = "<p>".$saudacao.","; 
						
						
						$corpo .= $dados['corpo'];
						
						$corpo = str_replace('$login$', $_POST['login'],$corpo);
						$corpo = str_replace('$senha$', $_POST['senha'],$corpo);
						$corpo = str_replace('$nome_usuario$', $_POST['nome_usuario'],$corpo);
						$corpo = str_replace('$generic$', $_POST['generic'],$corpo);
						
						$sql = mysql_query("select corpo from scop_envia_email where id = '0'");
						$dados = mysql_fetch_array($sql);

						$corpo .= $dados['corpo'];			
						$corpo = str_replace('$operador$', $_POST['operador'],$corpo);
						echo $corpo;
					?>


             </div>

       </div>
     </div>
