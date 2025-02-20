<?php
session_start();

// Verifica se o utilizador inicio sessão
if (!isset($_SESSION['email'])) {
    header("Location: index.php");
    exit();
}

include("ligacaoDB.php");

// Valida se os campos foram enviados e não estão vazios
if (empty($_POST["professor_id"])) {
    header("Location: glf.php");
    exit();
}

// Escapa os dados para evitar injeção de SQL
$id_professor = mysqli_real_escape_string($conn, $_POST["professor_id"]);

// Verifica se o id_professor é um número inteiro válido
if (!is_numeric($id_professor) || $id_professor <= 0) {
    $_SESSION["alert533"] = "Erro: ID de utilizador inválido!";
    header("Location: glf.php");
    exit();
}

// Prepara e executa a query de delete
$sql2 = "DELETE FROM utilizadores_to_exames WHERE id_utilizador = $id_professor";
$resultadoSQL2 = mysqli_query($conn, $sql2);

// Verifica se a execução da query falhou
if (!$resultadoSQL2) {
    $_SESSION["alert533"] = "Erro: Algo correu mal ao bloquear este utilizador!";
    header("Location: glf.php");
    exit();
}

// Fecha a conexão
mysqli_close($conn);

// Mensagem de sucesso
$_SESSION["alert533"] = "Utilizador removido com sucesso!";
header("Location: glf.php");
exit();
?>
