<?php
include("conexao.php");

ini_set('display_errors', 'Off');
$msg_erro = array();
$msg_success = array();

// Verifica se o formulário foi enviado e quais campos que foram
if (isset($_POST["submit"])) {
    // Coleta os dados do formulário
    $numero_os = $_POST["numero_os"];
    $data_abertura = $_POST["data_abertura"];
    $consumidor = $_POST["consumidor"];

    $cpf = $_POST["cpf"];
    $referencia = $_POST["referencia"];
    $descricao = $_POST["descricao"];

    if(strlen($numero_os) == 0){
    	$msg_erro['msg'][] = "Informe o número da OS!";
    }
    if(strlen($data_abertura) == 0){
    	$msg_erro['msg'][] = "Informe a Data de Abertura!";
    }
     if(strlen($consumidor) == 0){
    	$msg_erro['msg'][] = "Informe o Nome do Consumidor!";
    }
    if(strlen($cpf) == 0){
    	$msg_erro['msg'][] = "Informe o CPF do Consumidor!";
    }
    if(strlen($referencia) == 0){
    	$msg_erro['msg'][] = "Informe a referência do Produto!";
    }
    if(strlen($descricao) == 0){
    	$msg_erro['msg'][] = "Informe a descrição do Produto!";
    }
    if(count($msg_erro) == 0){
	    // Valida se Produto Existe
	    $sqlProduto = "
	    SELECT * FROM tbl_produto 
	    WHERE referencia = '$referencia' and descricao = '$descricao'";
	    $resultProduto = pg_query($conn, $sqlProduto);

	    if(strlen(pg_last_error() > 0)){
	    	$msg_erro['msg'][] = "Erro ao Cadastrar OS #0";
	    } else{

	    	// Pega ID do produto se ele existir
	    	if(pg_num_rows($resultProduto) > 0){
	    		$id_produto = pg_fetch_result($resultProduto, 0, 'id_produto');
	    	} else {
	    		$msg_erro['msg'][] = "Produto não existe!";
	    	}

	    }
	    //Validar se cliente existe
	    $sqlCliente = "Select * FROM tbl_cliente WHERE cpf = '$cpf'";
	    $resultCliente = pg_query($conn, $sqlCliente);

	    if(strlen(pg_last_error() > 0)){
			$msg_erro['msg'][] = "Erro ao Cadastrar OS #1";
		}else {
			//Pega o ID do cliente se ele existir
			if(pg_num_rows($resultCliente) > 0){
				$id_cliente = pg_fetch_result($resultCliente, 0, 'id_cliente');
			}else {
				$insertCliente = "
				INSERT INTO tbl_cliente (nome, cpf)
				VALUES ('$consumidor', '$cpf') RETURNING id_cliente";
				$result = pg_query($conn, $insertCliente);

				if(strlen(pg_last_error() > 0)){
					$msg_erro['msg'][] = "Erro ao Cadastrar OS #2";
				}else{
					$id_cliente = pg_fetch_result($result, 0, 'id_cliente');
				}
			}
		}
		if(count($msg_erro) == 0){
			$insertOS = "
			INSERT INTO tbl_os (numero_os, data_abertura, consumidor, cpf, id_produto) 
			VALUES ('$numero_os', '$data_abertura', '$consumidor', '$cpf', '$id_produto')";
			$result = pg_query($conn, $insertOS);

			if(strlen(pg_last_error() > 0)){
				$msg_erro['msg'][] = "Erro ao Cadastrar OS #1";
			}else{
				$msg_success['msg'][] = "OS $numero_os cadastrada com Sucesso!";
			}
		}

	}
	
}
	// Fazendo consulta de produtos no banco
	$sqlProdutos = "Select * FROM tbl_produto";
	$resultProdutos = pg_query($conn, $sqlProdutos);

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
	<title>
		Abertura de Ordem de Serviço
	</title>
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
    $(document).ready(function() {
        // Adiciona um evento de clique às linhas da tabela de produtos
        $("#modalProdutos tbody").on("click", "tr", function() {
            // Obtém os dados do produto da linha clicada
            var referencia = $(this).find("td:eq(0)").text();
            var descricao = $(this).find("td:eq(1)").text();

            // Preenche o input "Produto" com os dados do produto selecionado
            $("#referencia").val(referencia);
            $("#descricao").val(descricao);

            // Fecha o modal de produtos
            $("#modalProdutos").modal("hide");
        });
    });
</script>
<div class="modal fade" id="modalProdutos" tabindex="-1" aria-labelledby="modalProdutosLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalProdutosLabel">Selecionar Produto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
            	<table class="table">
            		<thead>
            			<tr>
            				<th>Referência</th>
            				<th>Descrição</th>
            				<th>Status</th>
            				<th>Tempo Garantia</th>
            			</tr>
            		</thead>
            		<tbody>
            			<?php
            			while ($row = pg_fetch_assoc($resultProdutos)) {
            				echo "<tr>";
            				echo "<td>{$row['referencia']}</td>";
            				echo "<td>{$row['descricao']}</td>";
            				echo "<td>{$row['status']}</td>";
            				echo "<td>{$row['tempo_garantia']}</td>";
            			}
            			?>
            		</tbody>
            	</table>
            </div>
        </div>
    </div>
</div>
	<div class="container box">
		<h2 class="text-center mb-4">Cadastro de OS</h2>
		<!-- DIV abaixo para caso der erro em tela -->
        <?php if (count($msg_erro) > 0){ ?>
            <div id="alert-danger" class="alert alert-danger"><?php echo implode("<br>", $msg_erro['msg']); ?></div>
        <?php } ?>
        <!-- DIV abaixo para caso produto for cadastrado com sucesso -->
        <?php if(count($msg_success) > 0){ ?>
            <div id="alert-success" class="alert alert-success"><?php echo implode("<br>", $msg_success['msg']) ?></div>
        <?php } ?>
		<form method="POST" action="" class="form-floating">
			<div class="row mb-3">
				<div class="col">
					<label for="numero_os" class="label">Número OS</label><br>
					<input type="text" name="numero_os" id="numero_os" value="<?php echo $numero_os?>" class="form-control">
				</div>
    			<div class="col">
    				<label for="data_abertura" class="label">Data Abertura</label><br>
					<input type="date" name="data_abertura" id="data_abertura" value="<?php echo $data_abertura?>" class="form-control">
    			</div>
  				<div class="row mb-3">
    				<div class="col">
    					<label for="consumidor" class="label">Nome Consumidor</label><br>
						<input type="text" name="consumidor" id="consumidor" value="<?php echo $consumidor?>" class="form-control">
	    			</div>
    				<script>
					$(document).ready(function() {
						$('#cpf').mask('000.000.000-00', {reverse: true});
					});
					</script>
	    			<div class="col-4">
	    				<label for="cpf" class="label">CPF Consumidor</label><br>
						<input type="text" name="cpf" id="cpf" value="<?php echo $cpf?>" class="form-control">
	    			</div>
	    		</div>
	    		<div class="row mb-3">
			        <div class="col">
			            <label for="referencia" class="form-label">Referência</label>
			                <div class="input-group">
			                    <input type="text" name="referencia" id="referencia" value="<?php echo $referencia?>" class="form-control">
			                    <button class="btn btn-outline-secondary" type="button" id="btnModalProduto" data-bs-toggle="modal" data-bs-target="#modalProdutos">
			                    	<i class="bi bi-search"></i>
			                    </button>
			                </div>
			        </div>
			                <div class="col">
			                	<label for="produto" class="form-label">Produto</label>
			                	<div class="input-group">
			                		<input type="text" name="descricao" id="descricao" value="<?php echo $descricao?>" class="form-control">
			                    	<button class="btn btn-outline-secondary" type="button" id="btnModalProduto" data-bs-toggle="modal" data-bs-target="#modalProdutos">
			                    	<i class="bi bi-search"></i>
			                    	</button>
			                	</div>
			            	</div>
			        <div class="row">
	    				<div class="col text-center"><br>
	    					<button type="submit" name="submit" id="submit" class="btn btn-primary" alt="Gravar Formulário">Gravar</button>
						</div>
	  				</div>
				</div>
			</form>
</body>
</html>