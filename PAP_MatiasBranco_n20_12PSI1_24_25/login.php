<?php
session_start();
include("ligacaoDB.php");

if (isset($_POST["email"]) && isset($_POST["pwd"])) {
    // Sanitizar os dados de entrada
    $email = test_input($_POST["email"]);
    $pwd = test_input($_POST["pwd"]);

    // Encriptar a palavra-passe
    $pwdHASH = hash('sha512', $pwd);

    // Verifica se o email existe
    $sql1 = "SELECT * FROM utilizadores WHERE email = '$email'";
    $resultados1 = mysqli_query($conn, $sql1);

    if (!$resultados1) {

        // Redireciona para index.php com erro genérico
        header("Location: index.php?erro=4");
        exit();
    }

    if (mysqli_num_rows($resultados1) > 0) {
        $rows = mysqli_fetch_array($resultados1);

        if($pwdHASH == $rows["pwd"]){

            if($rows["Ativo"] == "Sim"){

                // Guardar informações na sessão
                $_SESSION['email'] = $email;
                $_SESSION['Tipo_ut'] = $rows['tipo_ut'];

                // Redirecionar para a página inicial
                header("Location: menu.php");
                exit();
            }
            else{
                $sqlEmail="SELECT * FROM utilizadores WHERE email='".$_POST["email"]."'";
                $result = mysqli_query($conn, $sqlEmail);
                $rows = mysqli_fetch_array($result);
            
                $sqlError2="INSERT INTO logs (id_pessoa, name_log, erro_203, data) VALUES (".$rows["id"].", 'ERROR: Login', 'ERRO: O utilizador com o email- ".$rows["email"]." e id-".$rows["id"]." tentou efetuar login porém a conta do mesmo não está autualizado', NOW())";
                mysqli_query($conn, $sqlError2);
                header("Location: index.php?erro=3");
                exit();
            }
        }
        else{
            $sqlEmail="SELECT * FROM utilizadores WHERE email='".$_POST["email"]."'";
            $result = mysqli_query($conn, $sqlEmail);
            $rows = mysqli_fetch_array($result);
        
            $sqlError2="INSERT INTO logs (id_pessoa, name_log, erro_203, data) VALUES (".$rows["id"].", 'ERROR: Login', 'ERRO: O utilizador com o email- ".$rows["email"]." e id-".$rows["id"]." tentou efetuar login porém errou a palavra-passe!', NOW())";
            mysqli_query($conn, $sqlError2);
            header("Location: index.php?erro=2");
            exit();
        }
    } else {
        $sqlError2="INSERT INTO logs (id_pessoa, name_log, erro_203, data) VALUES (".$rows["id"].", 'ERROR: Login', 'ERRO: Um desconhecido tentou efetuar login com um email que não está registado! O email do mesmo é ".$_POST["email"]."', NOW())";
        mysqli_query($conn, $sqlError2);
        // Redireciona se os campos de email ou senha não forem enviados
        header("Location: index.php?erro=1");
        exit();
    }
} else {
    $sqlEmail="SELECT * FROM utilizadores WHERE email='".$_POST["email"]."'";
    $result = mysqli_query($conn, $sqlEmail);
    $rows = mysqli_fetch_array($result);

    $sqlError2="INSERT INTO logs (id_pessoa, name_log, erro_203, data) VALUES (".$rows["id"].", 'ERROR: Login', 'ERRO: O utilizador com o email- ".$rows["email"]." e id-".$rows["id"]." tentou efetuar login porém algo correu mal!".mysqli_error($conn)."', NOW())";
    mysqli_query($conn, $sqlError2);
    // Redireciona se os campos de email ou senha não forem enviados
    header("Location: index.php?erro=4");
    exit();
}

// Função para sanitizar os dados de entrada
function test_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
?>
