<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exame Vigio</title>
    <link rel="icon" type="image/x-icon" href="imgs/logo.png">
</head>
<body>
</body>
</html>
<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $passwordRecuperacao = $_POST['password'];
    $token = $_POST['token'];

    // Validar se a password não está vazia
    if (empty($passwordRecuperacao) || empty($token)) {
        die('Token ou palavra-passe inválidos.');
    }

    // Conectar á base de dados
    include("ligacaoDB.php");
  
    // Verificar o token
    $query = "SELECT email FROM password_resets WHERE token = '$token' AND created_at > NOW() - INTERVAL 1 MINUTE";
    $result = mysqli_query($conn, $query);

    
    if (mysqli_num_rows($result) === 0) {
        die('Token inválido ou expirado!');
    }

    $row = mysqli_fetch_assoc($result);
    $email = $row['email'];

    // Atualizar a password do utilizador
    $pwdNOVA = hash('sha512',$passwordRecuperacao);


    $query = "UPDATE utilizadores SET pwd = '$pwdNOVA' WHERE email = '$email'";
    if (mysqli_query($conn, $query)) {
        // Remover o token
        $query = "DELETE FROM password_resets WHERE token = '$token'";
        mysqli_query($conn, $query);

        $_SESSION["ERROR3204"] = 'Senha redefinida com sucesso!';
        header("Location: index.php");
        exit();
    } else {
        $_SESSION["ERROR3204"] = 'Erro ao redefinir a senha: ' . mysqli_error($conn);
        header("Location: reiniciar_password.php");
        exit();
    }

    mysqli_close($conn);
}
?>
