<?php
include("conexao.php");

ini_set('display_errors', 'Off');
$msg_erro = array();
$msg_success = array();

// Verifica se o formulário foi enviado
if (isset($_POST["submit"])) {
    // Coleta os dados do formulário
    $nome = $_POST["nome"];
    $cpf = $_POST["cpf"];
    $endereco = $_POST["endereco"];

    	//Exige o preenchimento dos campos
	    if(strlen($nome) == 0){
	    	$msg_erro['msg'][] = "Informe o Nome do Cliente!";
	    }
	    if(strlen($cpf) == 0){
	    	$msg_erro['msg'][] = "Informe o CPF do Cliente!";
	    }
	    if(strlen($endereco) == 0){
	    	$msg_erro['msg'][] = "Informe o Endereço do Cliente!";
	    }

	    //Verifica se o CPF do cliente já existe no banco
	    $sqlValidar = "SELECT * FROM tbl_cliente WHERE cpf = '$cpf'";
	    $resultValidar = pg_query($conn, $sqlValidar);

	    if (pg_num_rows($resultValidar) > 0){
			$msg_erro['msg'] = "Cliente já Cadastrado";
	    } else {
	    	//Se não tiver cadastrado, cadastra
	    	if(count($msg_erro) == 0){
		    	$insertCli = "
		    		INSERT INTO tbl_cliente (nome, cpf, endereco) 
		    		VALUES ('$nome', '$cpf', '$endereco')";
		    	$result = pg_query($conn, $insertCli);

		    	if(strlen(pg_last_error() > 0)){
		    		$msg_erro['msg'][] = "Erro ao Cadastrar Cliente!";
		    	}else {
		    		$msg_success['msg'][] = "Cliente Cadastrado com Sucesso!";
		    	}
	    	}
	    }
    }
    // Se clicar em "Listar Clientes" faz select na tabela de clientes (ainda não feito)
    if (isset($_POST["listar"])){
	$sql = "SELECT * FROM tbl_cliente";
	$res = pg_query($conn, $sql);

	$result_cliente = pg_fetch_all($res);
}
	// Se o ID do cliente não for vazio, ou seja, existir produto, tras os campos em tela
	$id_cliente = $_GET['cliente'];

	if (!empty($id_cliente)){
		$sql = "SELECT * FROM tbl_cliente WHERE id_cliente = $id_cliente";
		$res = pg_query($conn, $sql);

		if (pg_num_rows($res) > 0){
			$nome = pg_fetch_result($res, 0, "nome");
			$cpf = pg_fetch_result($res, 0, "cpf");
			$endereco = pg_fetch_result($res, 0, "endereco");
		}
	}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
	<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
    <title>Cadastro de Cliente</title>>
    <style>
		.box{
			position: absolute;
			top: 50%;
			left: 50%;
			transform: translate(-50%, -50%);
			border: 3px solid black;
			padding: 15px;
		}
		.inputBox{
			position: relative;
		}
	</style>
</head>
<body>
	<script>
		$(function(){
			setTimeout(function(){
        		$(".alert-danger, .alert-success").hide();
    		}, 3000); // 3000 milissegundos = 3 segundos
		});
		$(document).ready(function(){
			$('#cpf').mask('000.000.000-00', {reverse: true});
		});
	</script>
	<div class="container box">
        <h2 class="text-center mb-4">Cadastro de Cliente</h2>
        <!-- DIV abaixo para caso der erro em tela -->
		<?php if (count($msg_erro) > 0){ ?>
				<div id="alert-danger" class="alert alert-danger"><?php echo implode("<br>", $msg_erro['msg']); ?></div>
		<?php } ?>
		<!-- DIV abaixo para caso produto for cadastrado com sucesso -->
		<?php if(count($msg_success) > 0){ ?>
		<div id="alert-success" class="alert alert-success"><?php echo implode("<br>", $msg_success['msg']) ?></div>
		<?php } ?>
        <form method="POST" action="" class="form-floating">
            <div class="form-group">
                <label for="nome" class="labelInput">Nome</label>
                <input type="text" name="nome" id="nome" value="<?php echo $nome?>" class="form-control">
            </div>
            <div class="form-group">
                <label for="cpf" class="labelInput">CPF</label>
                <input type="text" name="cpf" id="cpf" value="<?php echo $cpf?>" class="form-control">
            </div>
            <div class="form-group">
                <label for="endereco" class="labelInput">Endereço</label>
                <input type="text" name="endereco" id="endereco" value="<?php echo $endereco?>" class="form-control">
            </div>
            <div class="row">
	    		<div class="col text-center"><br>
	    			<button type="submit" name="submit" id="submit" class="btn btn-primary" alt="Gravar Formulário">Gravar</button>
				</div>
	  		</div>
        </form>
            </div>
        </div>
    </div>
</body>
</html>