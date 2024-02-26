<?php
include("conexao.php");

// Eliminar erros WARNING da tela
ini_set('display_errors', 'Off');

//Criar variável de erro
$msg_erro = array();
$msg_success = array();

// Verifica se o formulário foi enviado
if (isset($_POST["submit"])) {
    // Pega dados do form
    $referencia = $_POST["referencia"];
    $descricao  = $_POST["descricao"];
    $tempo_garantia = isset($_POST["tempo_garantia"]) ? intval($_POST['tempo_garantia']) : 0;
    $status = (isset($_POST['status']) ? "t" : "f");
    $id_produto = $_POST["id_produto"];

    //Obriga Preencher os Campos
    if(strlen($descricao) == 0){
    	$msg_erro['msg'][] = "Informe a descrição do Produto!";
    }
    if(strlen($referencia) == 0){
    	$msg_erro['msg'][] = "Informe a referencia do Produto!";
    }
    // Se não houver erro e o ID do produto estiver vazio, executa o IF abaixo
    if(count($msg_erro) == 0){
    	if (empty($id_produto)){
		    //Verifica se a ref do produto já existe no banco
		    $sqlValidar = "Select * FROM tbl_produto WHERE referencia = '$referencia'";
		    $resultValidar = pg_query($conn, $sqlValidar);
		    
		    if (pg_num_rows($resultValidar) > 0){
				$msg_erro['msg'][] = "Produto já Cadastrado";
		    } else {
		    	if(count($msg_erro) == 0){
			    	$insertProd = "
			    		INSERT INTO tbl_produto (referencia, descricao, status, tempo_garantia) 
			    		VALUES ('$referencia', '$descricao', '$status', '$tempo_garantia')";
			    	$result = pg_query($conn, $insertProd);

			    	if(strlen(pg_last_error() > 0)){
			    		$msg_erro['msg'][] = "Erro ao Cadastrar Produto!";
			    	}else {
			    		$msg_success['msg'][] = "Produto Cadastrado com Sucesso!";
			    	}
		    	}
		    }
		    //Senão, faz update atualizando o produto
	    }else{
	    	$sql_update = "UPDATE tbl_produto SET descricao = '$descricao', referencia = '$referencia', tempo_garantia = '$tempo_garantia', status = '$status' WHERE id_produto = $id_produto";
	    	$res_update = pg_query($conn, $sql_update);

	    	if (strlen(pg_last_error()) > 0){
	    		$msg_erro["msg"][] = "Erro ao Atualizar Produto!";
	    	}else{
	    		$msg_success["msg"][] = "Produto $referencia Atualizado com Sucesso";
	    	}

	    }
    }
}

	// Se clicar em "Listar Produtos" faz select na tabela de produtos
	if (isset($_POST["listar"])){
		$sql = "SELECT * FROM tbl_produto";
		$res = pg_query($conn, $sql);
		$result_produto = pg_fetch_all($res);
	}

	$id_produto = $_GET['produto'];
	// Se o ID do produto não for vazio, ou seja, existir produto, tras os campos em tela
	if (!empty($id_produto)){
		$sql = "SELECT * FROM tbl_produto WHERE id_produto = $id_produto";
		$res = pg_query($conn, $sql);

		if (pg_num_rows($res) > 0){
			$referencia = pg_fetch_result($res, 0, "referencia");
			$descricao = pg_fetch_result($res, 0, "descricao");
			$tempo_garantia = pg_fetch_result($res, 0, "tempo_garantia");
			$status = pg_fetch_result($res, 0, "status");
		
			if ($status == "t"){
				$checked = "checked";
			}else{
				$checked = "";
			}
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
	<title>
		Cadastro Produto
	</title>
	<style>

		.box {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            border: 3px solid black;
            padding: 15px;
            width: 80%;
        }

        .inputBox {
            position: relative;
        }

        .table-container {
            clear: both; 
            margin-top: 40%;
            border: 3px solid black;
        }

	</style>
</head>
<body>
	<script>
		//Função que faz o alerta durar por 3 segundos.
		$(function(){
			setTimeout(function(){
        		$(".alert-danger, .alert-success").hide();
    		}, 3000); // 3000 milissegundos = 3 segundos

			//Função que faz o campo "Tempo Garantia" não permitir caracter que não seja número
    		$('#tempo_garantia').on('input', function() {
	    		var valor = $(this).val();
	    		$(this).val(valor.replace(/[^0-9]/g, ''));
			});
		});
	</script>
	<div class="container box">
        <h2 class="text-center mb-4">Cadastro de Produto</h2>
        <!-- DIV danger para caso der erro em tela -->
        <?php if (count($msg_erro) > 0){ ?>
            <div id="alert-danger" class="alert alert-danger"><?php echo implode("<br>", $msg_erro['msg']); ?></div>
        <?php } ?>
        <!-- DIV success para mensagens de sucesso -->
        <?php if(count($msg_success) > 0){ ?>
            <div id="alert-success" class="alert alert-success"><?php echo implode("<br>", $msg_success['msg']); ?></div>
        <?php } ?>
        <form method="POST" action="" class="form-floating">
        	<input type="hidden" name="id_produto" value="<?php echo $id_produto?>"> 
            <div class="row mb-3">
                <div class="col">
                    <label for="referencia" class="label">Referência Produto</label><br>
                    <input type="text" name="referencia" id="referencia" value="<?php echo $referencia; ?>" class="form-control">
                </div>
                <div class="col">
                    <label for="descricao" class="label">Descrição Produto</label><br>
                    <input type="text" name="descricao" id="descricao" value="<?php echo $descricao; ?>" class="form-control">
                </div>
                <div class="row mb-3">
                    <div class="col">
                        <label for="tempo_garantia" class="label" step="1">Tempo Garantia</label><br>
                        <input type="text" name="tempo_garantia" id="tempo_garantia" value="<?php echo $tempo_garantia?>" class="form-control">
                    </div>
                    
                </div>
                <div class="row mb-3">
                    <div class="col-4">
                        <label for="status" class="label">Ativo</label><br>
                        <input type="checkbox" name="status" <?php echo $checked; ?> value="t" id="status">
                    </div>
                </div>
                <div class="row">
                    <div class="col text-center"><br>
                        <?php 
                    	//Se o id_produto for maior que 0, o botão em tela muda para "Atualizar"
                    	if (strlen($id_produto) > 0){
                    		$btn = "Atualizar";
                    		//Senão, continua em "Gravar"
                    		}else{
	                    		$btn = "Gravar";
	                    	}
                        ?>
                        <button type="submit" name="submit" id="submit" class="btn btn-primary" alt="Gravar Formulário"><?php echo $btn; ?></button>

                        <button type="submit" name="listar" class="btn btn-info" id="listarProdutos">Listar Produtos</button>
                    	
                    	<?php if (strlen($id_produto) >0) { ?>
                    		<a href="cadastro_produto.php" class="btn btn-success">Limpar</a>
                    	<?php } ?>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <?php if(count($result_produto) > 0){?>
	    <div class="container table-container">
	        <table class="table">
	            <thead>
	                <tr>
	                    <th>Referência</th>
	                    <th>Descrição</th>
	                    <th>Status</th>
	                    <th>Tempo Garantia</th>
	                    <th>Ações</th>
	                </tr>
	            </thead>
	            <tbody>
	                <?php
	                foreach ($result_produto as $key => $value) {
	                	echo "<tr>";
	                    echo "<td>{$value['referencia']}</td>";
	                    echo "<td>{$value['descricao']}</td>";
	                    echo "<td>{$value['status']}</td>";
	                    echo "<td>{$value['tempo_garantia']}</td>";
	                	echo "<td><a href='cadastro_produto.php?produto=".$value['id_produto']."'>Alterar</a></td>";
	                	echo "</tr>";
	                }
	                ?>
	            </tbody>
	        </table>
	    </div>
	<?php } ?>
</body>
</html>