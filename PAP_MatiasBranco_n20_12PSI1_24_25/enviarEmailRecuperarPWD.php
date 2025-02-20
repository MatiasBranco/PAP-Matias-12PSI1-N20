<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exame Vigio</title>
    <link rel="icon" type="image/x-icon" href="imgs/logo.png">
</head>
<body>
    
<?php
session_start();
// Conectar á base de dados
include("ligacaoDB.php");

$sqlVerify = "SELECT * FROM utilizadores WHERE email = '".$_POST["email"]."'";
$resultadoSQLVerify = mysqli_query($conn, $sqlVerify);
$numero = mysqli_num_rows($resultadoSQLVerify);
$email = $_POST["email"];
if($numero !=1){
    $_SESSION["ERROR3205"] = "Ocorreu um erro ao tentar fazer a recuperação da palavra-passe!";

    $sqlEmail="SELECT * FROM utilizadores WHERE email='".$email."'";
    $result = mysqli_query($conn, $sqlEmail);
    $rows = mysqli_fetch_array($result);

    $sqlError = "INSERT INTO logs (id_pessoa, name_log, erro_203, data) VALUES (".$rows["id"].", 'Erro ao Enviar email de Recuperação', '".mysqli_error($conn)."', NOW())";
    mysqli_query($conn, $sqlError);

    header("Location: recruperarPWD.php");
    exit();
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    if (!$email) {
        echo 'E-mail inválido!';
        $sqlError1="INSERT INTO logs (name_log, erro_203, data) VALUES ('Erro ao Enviar email de Recuperação de palavra-passe', 'Email Inválido - $email tentou fazer a reposição da sua palavra-passe no entanto o email não econsta na base de dados!', NOW())";
        mysqli_query($conn, $sqlError1);
    }

    // Fazer a conexão á base de dados
    include("ligacaoDB.php");

    // Gerar um token seguro
    $token = bin2hex(random_bytes(32));
    $createdAt = date('Y-m-d H:i:s');

    // Inserir o token na base de dados
    $query = "INSERT INTO password_resets (email, token, created_at) VALUES ('$email', '$token', '$createdAt')";
    if (mysqli_query($conn, $query)) {
        // Criar o link de redefinição
        $resetLink = "http://localhost/ESCOLA/Redes/Trabalhos%20Finais/PAP_MatiasBranco_n20_12PSI1_24_25/reiniciar_password.php?token=$token";

       // Enviar o e-mail
        $subject = 'Redefinição de Palavra-passe';

        // Corpo do e-mail com HTML e Bootstrap
        $message = '
        <!DOCTYPE html>
        <html lang="pt">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Redefinição de Palavra-passe</title>
            <!-- Bootstrap CSS -->
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        </head>
        <body style="background-color: #f9f9f9; margin: 0; padding: 0; font-family: Arial, sans-serif;">
            <div class="container mt-5">
                <div class="row justify-content-center">
                    <div class="col-lg-6">
                        <div class="card shadow-sm">
                            <div class="card-body text-center">
                                <h2 class="card-title mb-4 text-primary">Redefinição de Palavra-passe</h2>
                                <p class="card-text mb-4">
                                    Recebemos um pedido para redefinir a sua palavra-passe. Clique no botão abaixo para proceder.
                                </p>
                                <!-- Botão de redefinição -->
                                <a href="' . $resetLink . '" 
                                class="btn btn-primary btn-lg" 
                                style="text-decoration: none; color: white; background-color: #007bff; padding: 10px 20px; border-radius: 5px; display: inline-block;">
                                    Redefinir Palavra-passe
                                </a>
                                <hr class="my-4">
                                <p class="small text-muted">
                                    Caso não tenha solicitado esta redefinição, ignore este e-mail. O link expirará em 1 hora.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </body>
        </html>';

        // Cabeçalhos do e-mail
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= 'From: no-reply@examevigio.com' . "\r\n";



        if (mail($email, $subject, $message, $headers)) {
            echo ' <script>
                        // Função que redireciona após 20 segundos
                        setTimeout(function() {
                            window.location.href = "https://mail.google.com/mail/u/0/?tab=rm&ogbl#inbox"; // Substitua pela URL de destino
                        }, 1000); // 1000 ms = 1 segundo
                   </script>';
            
            $sqlEmail="SELECT * FROM utilizadores WHERE email='".$email."'";
            $result = mysqli_query($conn, $sqlEmail);
            $rows = mysqli_fetch_array($result);
        
            $sqlError = "INSERT INTO logs (id_pessoa, name_log, erro_203, data) VALUES (".$rows["id"].", 'Sucesso ao Enviar email de Recuperação', 'O email de recuperação de conta foi enviado perfeitamente para o email $email!', NOW())";
            mysqli_query($conn, $sqlError);
        } else {
            $_SESSION["ERROR3205"] = 'Erro ao enviar o e-mail.';

            $sqlEmail="SELECT * FROM utilizadores WHERE email='".$email."'";
            $result = mysqli_query($conn, $sqlEmail);
            $rows = mysqli_fetch_array($result);

            $sqlError2="INSERT INTO logs (id_pessoa, name_log, erro_203, data) VALUES ( ".$rows["id"].",'Erro ao Enviar email de Recuperação de palavra-passe', 'Erro ao enviar o email!', NOW())";
            mysqli_query($conn, $sqlError2);
            header("Location: recruperarPWD.php");
            exit();
        }
    } else {
        $_SESSION["ERROR3205"] = 'Erro ao salvar o token!';

        $sqlEmail="SELECT * FROM utilizadores WHERE email='".$email."'";
        $result = mysqli_query($conn, $sqlEmail);
        $rows = mysqli_fetch_array($result);

        $sqlError3="INSERT INTO logs (id_pessoa, name_log, erro_203, data) VALUES (".$rows["id"].", 'Erro ao Salvar o token de recuperaçao', 'Erro ao salvar o token!', NOW())";
        mysqli_query($conn, $sqlError3);
        header("Location: recruperarPWD.php");
        exit();
    }

    mysqli_close($conn);
}
?>
</body>
</html>
