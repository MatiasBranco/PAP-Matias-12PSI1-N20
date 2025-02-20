<?php
include("ligacaoDB.php");

if(!isset($_SESSION["email"])){
    header("index.php");
    exit();
}

if (isset($_POST["email"]) && isset($_POST["codigo"])) {
    $email = $_POST["email"];
    $codigo = $_POST["codigo"];
    $enviaremail = false;
    
    // Loop
    do {
        // Ver se o codigo existe na tabela 'cpa' 
        $sqlCodigo = "SELECT * FROM cpa WHERE token='$codigo'";
        $resultCodigo = mysqli_query($conn, $sqlCodigo);

        if (mysqli_num_rows($resultCodigo) == 0) {
            // Se o codigo não existe vamos inserilo lá
            $sqlInsert = "INSERT INTO cpa (token, email) VALUES ('$codigo', '$email')";
            $resultado = mysqli_query($conn, $sqlInsert);
            $enviaremail = true;
            break; // Sair do loop
        } else {
            // Caso exista geramos um codigo denovo e fazemos o processo todo de novo
            $codigo = geradorCriarCodigoProfessor();
            $enviaremail = false;
        }
    } while (!$enviaremail);

    // Enviar email quando tudotiver certo
    if ($enviaremail) {
        $recebedor = $email;
        $subject = "Convite de Admissão - ExameVigio";
        $body = '
        <!DOCTYPE html>
        <html lang="pt-BR">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Convite de Admissão</title>
        </head>
        <body style="font-family: Arial, sans-serif; padding: 20px; background-color: #f8f9fa; color: #333;">
            <div style="max-width: 600px; margin: 0 auto; background-color: #fff; padding: 20px; border-radius: 5px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);">
                <h1 style="font-size: 24px; color: #007bff;">Convite de Admissão - ExameVigio</h1>
                <p style="font-size: 16px; line-height: 1.5;">
                    Olá, <br> Você foi convidado para se juntar à plataforma ExameVigio. Use o código abaixo para verificar e criar a sua conta:
                </p>
                <div style="text-align: center; margin: 20px 0; padding: 10px; background-color: #f0f0f0; border: 1px dashed #007bff; border-radius: 5px;">
                    <p style="font-size: 18px; font-weight: bold; color: #007bff;">' . $codigo . '</p>
                </div>
                <p style="font-size: 16px; line-height: 1.5;">
                    Clique no botão abaixo para aceder à página de verificação:
                </p>
                <div style="text-align: center; margin: 20px 0;">
                    <a href="http://localhost/ESCOLA/Redes/Trabalhos%20Finais/PAP_MatiasBranco_n20_12PSI1_24_25/CodeVerifyPAGE.html" target="_blank" style="display: inline-block; background-color: #007bff; color: #fff; text-decoration: none; padding: 10px 20px; font-size: 16px; border-radius: 5px;">
                        Visite o Site
                    </a>
                </div>
                <p style="font-size: 14px; color: #666;">Esperamos por si!</p>
            </div>
        </body>
        </html>';
        
        $headers = "Content-Type: text/html; charset=UTF-8\r\n";
        
        if (mail($recebedor, $subject, $body, $headers)) {
            echo "Email enviado com sucesso para $recebedor";
            //sleep(10);
            
            header("Location: menu.php");
            exit();
        } else {
            echo "ERRO: Ocorreu um erro ao enviar o email!";
            $sqlEmail="SELECT * FROM utilizadores WHERE email='".$_SESSION["email"]."'";
            $result = mysqli_query($conn, $sqlEmail);
            $rows = mysqli_fetch_array($result);

            $sqlError="INSERT INTO logs (id_pessoa, name_log, erro_203, data) VALUES ( ".$rows["id"].",'Erro ao Enviar email de Junção', '".mysqli_error($conn)." ||||| O email da pessoa que quiz enviar o código foi ".$_SESSION["email"]."!', NOW()";
            mysqli_query($conn, $sqlError);
        }
    }

    // fechar a conexão á base de dados
    mysqli_close($conn);
}
else{
    echo "Erro ao enviar Email!";
    $sqlError="INSERT INTO logs (name_log, erro_203, data) VALUES ('Erro ao Enviar email de Junção', '".mysqli_error($conn)."', NOW()";
    mysqli_query($conn, $sqlError);
}

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
