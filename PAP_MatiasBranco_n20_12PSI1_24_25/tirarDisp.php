<?php
session_start();

// Verifica se o utilizador está logado
if (!isset($_SESSION['email'])) {
    header("Location: index.php");
    exit();
}

include("ligacaoDB.php");

// Valida se os campos foram enviados e não estão vazios
if (empty($_POST["professor_id"]) || empty($_POST["horario"])) {
    header("Location: glf.php");
    exit();
}

// Escapa os dados para evitar injeção de SQL
$id_professor = mysqli_real_escape_string($conn, $_POST["professor_id"]);
$horario = mysqli_real_escape_string($conn, $_POST["horario"]);

// Verifica se o professor já está registrado
$sql1 = "SELECT * FROM utilizadores_to_exames WHERE id_utilizador = '$id_professor'";
$resultadoSQL1 = mysqli_query($conn, $sql1);

if (!$resultadoSQL1) {
    $_SESSION["alert333"] = "Erro: Algo correu mal ao bloquear este utilizador!";
    exit();
}

if (mysqli_num_rows($resultadoSQL1) > 0) {
    mysqli_free_result($resultadoSQL1);
    mysqli_close($conn);
    $_SESSION["alert333"] = "Erro: Algo correu mal ao bloquear este utilizador!";
    header("Location: glf.php");
    exit();
}

// Insere os dados na tabela
$sql2 = "INSERT INTO utilizadores_to_exames (id_utilizador, ate) VALUES ('$id_professor', '$horario')";
$resultadoSQL2 = mysqli_query($conn, $sql2);

if (!$resultadoSQL2) {
    $_SESSION["alert333"] = "Erro: Algo correu mal ao bloquear este utilizador!";
    exit();
}

// Fecha a conexão e redireciona
mysqli_close($conn);
$_SESSION["alert333"] = "Utilizador Bloqueado com sucesso!";
header("Location: glf.php");
exit();
?>
