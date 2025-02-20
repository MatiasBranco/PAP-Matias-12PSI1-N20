<?php
session_start(); // Inicia a sessão

// Verifica se a variável 'enviaremail' está configurada na sessão
if (isset($_SESSION["enviaremail"])) {
    if ($_SESSION["enviaremail"] === "enviar") {
        // Dados do e-mail
        $recebedor = $_SESSION["email"];
        $subject = "AVISO - ExameVigio";
        $body = '<!DOCTYPE html>
        <html lang="pt-BR">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>AVISO de Segurança</title>
        </head> 
        <body style="font-family: Arial, sans-serif; padding: 20px; background-color: #f8f9fa; color: #333;">
            <div style="max-width: 600px; margin: 0 auto; background-color: #fff; padding: 20px; border-radius: 5px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);">
                <h1 style="font-size: 24px; color:rgb(255, 0, 43);">AVISO de Segurança</h1>
                <p style="font-size: 16px; line-height: 1.5;">
                    Olá, <br> A palavra-passe da sua conta no Exame-Vigio foi alterada! Caso não o tenha efetuado sugerimos-lhe que mude a sua palavra passe para uma mais elaborada. Caso o problema persista, contacte o suporte ou a equipa técnica para a resolução do problema!
                </p>
                <p style="font-size: 14px; color: #666;">Departamento de Segurança!</p>
            </div>
        </body>
        </html>';

        // Configuração dos cabeçalhos do e-mail
        $headers = "From: Exame Vigio <no-reply@examevigio.com>\r\n";
        $headers .= "Reply-To: suporte@examevigio.com\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

        // Envio do e-mail
        if (mail($recebedor, $subject, $body, $headers)) {
            $sqlEmail="SELECT * FROM utilizadores WHERE email='".$_SESSION["email"]."'";
            $result = mysqli_query($conn, $sqlEmail);
            $rows = mysqli_fetch_array($result);

            $sqlError2="INSERT INTO logs (id_pessoa, name_log, erro_203, data) VALUES ( ".$rows["id"].",'E-mail de aviso ao torcar palavra-passe', 'E-Mail de Aviso enviado com sucesso!', NOW())";
            mysqli_query($conn, $sqlError2);
            header("Location: menu.php");
            exit();
        } else {
            echo "Erro ao enviar o e-mail. Verifique as configurações do servidor.";
            $sqlEmail="SELECT * FROM utilizadores WHERE email='".$_SESSION["email"]."'";
            $result = mysqli_query($conn, $sqlEmail);
            $rows = mysqli_fetch_array($result);

            $sqlError2="INSERT INTO logs (id_pessoa, name_log, erro_203, data) VALUES ( ".$rows["id"].",'Erro: E-mail de aviso ao torcar palavra-passe', 'Erro ao enviar o e-mail. Verifique as configurações do servidor: ".mysqli_error($conn)."', NOW())";
            mysqli_query($conn, $sqlError2);
        }
    } else {
        echo "Ação inválida!";
        $sqlEmail="SELECT * FROM utilizadores WHERE email='".$_SESSION["email"]."'";
        $result = mysqli_query($conn, $sqlEmail);
        $rows = mysqli_fetch_array($result);

        $sqlError2="INSERT INTO logs (id_pessoa, name_log, erro_203, data) VALUES ( ".$rows["id"].",'Erro: E-mail de aviso ao torcar palavra-passe', 'Ação inválida!: ".mysqli_error($conn)."', NOW())";
        mysqli_query($conn, $sqlError2);
    }
} else {
    $sqlEmail="SELECT * FROM utilizadores WHERE email='".$_SESSION["email"]."'";
    $result = mysqli_query($conn, $sqlEmail);
    $rows = mysqli_fetch_array($result);

    $sqlError2="INSERT INTO logs (id_pessoa, name_log, erro_203, data) VALUES ( ".$rows["id"].",'Erro: E-mail de aviso ao torcar palavra-passe', 'A sessão de enviar email não foi defenida ou houve algum erro!', NOW())";
    mysqli_query($conn, $sqlError2);
    header("Location: menu.php");
    exit();
}
?>
