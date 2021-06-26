//Criado por: Rafael Fernando Nazatto
//Se algum elemento não existir no html o JAVASCRIPT não irá encontra-lo e não será executado ate o fim


function optionCheck(){
					
	document.getElementById('id_nome_usuario').value='';
	document.getElementById('id_login').value='';
	document.getElementById('id_senha').value='';
	document.getElementById('id_generic').value='';
	document.getElementById('id_chamado').value='';
						
	var option = document.getElementById("options").value;
	var array = option.split(",");		

	document.getElementById('id_generic').placeholder=array[0];
						
	document.getElementById('nome_usuario').style.visibility ="hidden";
	document.getElementById('login').style.visibility ="hidden";
	document.getElementById('senha').style.visibility ="hidden";
	document.getElementById('generic').style.visibility ="hidden";

	document.getElementById("saudacao").style.visibility ="visible";
	document.getElementById("para").style.visibility ="visible";
	document.getElementById("cc").style.visibility ="visible";
	document.getElementById("cco").style.visibility ="visible";
	document.getElementById("chamado").style.visibility ="visible";

	if(array[2] == "a1" || array[3] == "a1" || array[4] == "a1" || array[5] == "a1"){
		document.getElementById('nome_usuario').style.visibility ="visible";
	}
	if(array[2] == "b1" || array[3] == "b1" || array[4] == "b1" || array[5] == "b1"){
		document.getElementById('login').style.visibility ="visible";
	}
	if(array[2] == "c1" || array[3] == "c1" || array[4] == "c1" || array[5] == "c1"){
		document.getElementById('senha').style.visibility ="visible";
	}
	if(array[2] == "d1" || array[3] == "d1" || array[4] == "d1" || array[5] == "d1"){
		document.getElementById('generic').style.visibility ="visible";
	}
}