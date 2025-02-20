<?php
session_start(); // Inicia a sessão

// Verifica se o utilizador está logado
if (!isset($_SESSION['email']) && $_SESSION['Tipo_ut']!="Admin") {
    header("Location: gerir_profs.php");
    exit();
}


// Verifica se o parâmetro de email foi enviado pela URL
if (!isset($_GET['email'])) {
    echo "<script>alert('Nenhum utilizador selecionado para edição!');</script>";
    header("Location: gerir_profs.php");
    exit();
}


include("ligacaoDB.php");

$email = $_GET['email'];

// Verifica se o utilizador existe na base de dados
$sqlBuscaUtilizador = "SELECT * FROM utilizadores WHERE email = '$email'";
$resultado = mysqli_query($conn, $sqlBuscaUtilizador);

if ($resultado && mysqli_num_rows($resultado) > 0) {
    $utilizador = mysqli_fetch_assoc($resultado);
} else {
    echo "<script>alert('Utilizador não encontrado!');</script>";
    header("Location: menu.php");
    exit();
}

// Atualizar os dados do utilizador
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = $_POST['nome'];
    $tipoUtilizador = $_POST['tipoUtilizador'];
    $idGr =$_POST['idGr'];
    $password = $_POST['password'];

    // Atualização na base de dados
    $sqlAtualizaUtilizador = "UPDATE utilizadores SET nome = '$nome', Tipo_ut = '$tipoUtilizador', id_gr = '$idGr' WHERE email = '$email'";

    if (mysqli_query($conn, $sqlAtualizaUtilizador)) {
        echo "<script>alert('Utilizador atualizado com sucesso!');</script>";
        header("Location: menu.php");
        exit();
    } else {
        echo "<script>alert('Erro ao atualizar utilizador!');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exame Vigio</title>
    <link rel="icon" type="image/x-icon" href="imgs/logo.png">
    <!-- Link para o CSS do Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Editar Utilizador</h2>
        <form method="POST" action="">
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" value="<?php echo $utilizador['email']; ?>" disabled>
            </div>
            <div class="mb-3">
                <label for="nome" class="form-label">Nome</label>
                <input type="text" class="form-control" id="nome" name="nome" value="<?php echo $utilizador['nome']; ?>" required>
            </div>
            <div class="mb-3">
                <label for="tipoUtilizador" class="form-label">Tipo de Utilizador</label>
                <input type="text" class="form-control" id="tipoUtilizador" name="tipoUtilizador" value="<?php echo $utilizador['Tipo_ut']; ?>" required>
            </div>
            <div class="mb-3">
                <label for="idGr" class="form-label">ID Grupos de Recrutamento</label>
                <input type="text" class="form-control" id="idGr" name="idGr" value="<?php echo $utilizador['id_gr']; ?>" maxlength="3" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Atualizar Ano Letivo: </label>
                <input type="button" class="btn btn-primary" name="btnAtualizarAno" id="btnAtualizarAno" value="Atualizar Ano Letivo">
            </div>
            <button type="submit" class="btn btn-primary">Salvar Alterações</button>
            <a href="menu.php" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
    <!-- Script do Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
