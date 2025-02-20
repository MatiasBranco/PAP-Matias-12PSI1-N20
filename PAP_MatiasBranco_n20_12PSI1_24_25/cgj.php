<?php
session_start(); // Inicia a sessão

// Verifica se o utilizador está logado
if (!isset($_SESSION['email']) && $_SESSION["Tipo_ut"] != "Admin") {
    // Se o utilizador não estiver logado, redireciona para a página de login
    header("Location: index.php");
    exit();
}

include("ligacaoDB.php");

// Garantir que a consulta para o tema só é feita se o email existir na sessão
$email = $_SESSION['email'] ?? null;
if ($email) {
    $sqlVerificarTema = "SELECT Tema FROM utilizadores WHERE email = '$email'";
    $resultadosVerificarTema = mysqli_query($conn, $sqlVerificarTema);
    
    // Verifica se a consulta retornou um resultado válido
    if ($resultadosVerificarTema && $row = mysqli_fetch_assoc($resultadosVerificarTema)) {
        $tema = $row["Tema"];
    } 
    else {
        $tema = "Normal"; // Valor padrão se o tema não for encontrado
    }

    // Define as cores com base no tema
    if ($tema == "Normal") {
        $bodyNavvy1 = "#343a40";
    } else {
        $bodyNavvy1 = "#1C1C1C";
    }
} else {
    // Caso o email não exista, redireciona para o login
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exame Vigio</title>
    <!-- Logo da Página Web -->
     
    <link rel="icon" type="image/x-icon" href="imgs/logo.png">    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://getbootstrap.com/docs/5.3/assets/css/docs.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</head>
<style>
    body{
        background-color: <?php echo $bodyNavvy1 ?>;
    }
    header {
        background-color: #007bff;
        color: white;
        padding: 20px 0;
    }
</style>
<body>
    <header class="text-center py-4">
        <h1>Portal Exame Vigio</h1>
        <p>Convide uma pessoa para juntar-se à nossa plataforma!</p>
    </header>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header text-center">
                        <h2>Criar Codigo de Junção</h2>
                    </div>
                    <!-- Formulário de preencher os dados -->
                    <div class="card-body">
                    <form name="formGerarCodigoJuncao" method="POST" action="enviarcodigoJuncao.php">
                        <!-- Email -->
                        <div class="mb-3">
                            <label for="email" class="form-label">Email da pessoa que deseja convidar *</label>
                            <input type="text" class="form-control" id="email" name="email" placeholder="me@exemplo.com">
                            <div id="erro1" style="color: red;"></div>
                        </div>
                        <div class="mb-3">
                            <label for="xd" class="form-label">Codigo *</label>
                            <input type="text" id="codigo" name="codigo" value="<?php echo geradorCriarCodigoProfessor(); ?>" readonly>
                            <div id="erro2" style="color: red;"></div>
                        </div>
                        <label style="font-size: 14px; color: red; font-weight: bold;">Todos os campos com (*) são obrigatórios!</label>

                        <!-- Botão confirmar dados -->
                        <input type="button" class="btn btn-primary w-100" onclick="VerificarCampos()" name="verific_btn" id="verific_btn" value="Confirmar">
                        <!-- Botão de envio -->
                        <button type="submit" class="btn btn-primary w-100" style="display: none" name="submeter_btn" id="submeter_btn">Enviar</button>
                        <br><br>
                        <input type="button" class="btn btn-primary w-100" onclick="window.location.href='menu.php'" name="cancelar_btn" id="cancelar_btn" value="Cancelar">
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
<!-- Link para o arquivo JavaScript "script1.js" -->
<script src="script2.js"></script>
<?php
function geradorCriarCodigoProfessor($tamanho = 5) {
    $caracteres = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $stringAleatoria = '';
    
    for ($i = 0; $i < $tamanho; $i++) {
        $indiceAleatorio = rand(0, strlen($caracteres) - 1);
        $stringAleatoria .= $caracteres[$indiceAleatorio];
    }
    
    return $stringAleatoria;
}

?>