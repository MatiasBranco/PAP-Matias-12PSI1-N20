<?php
    session_start(); // Inicia a sessão, necessário para usar $_SESSION

    include("ligacaoDB.php"); // Ligação à BD

    // Verificar se os dados foram submetidos corretamente
    if(isset($_POST["codVerifc"])) {

        //passar dados para as variaveis
        $codigo = mysqli_real_escape_string($conn, $_POST["codVerifc"]); // "mysqli_real_escape_string" torna caracteres especiais em uma string para tornar as injeções SQL seguras
//----------------------------------------------------QUERY 1----------------------------------------------------\\
        
        // Verifique se o codigo é existente na base de dados
        $sql = "SELECT * FROM cpa WHERE token = '$codigo'";
        $resultados = mysqli_query($conn, $sql);
        
        //se o resultados não tiver funcionado então ele diz o erro
        if(!$resultados) {
            die("Erro na consulta: " . mysqli_error($conn)); // Verifica possíveis erros na consulta
        }
//----------------------------------------------------QUERY 2----------------------------------------------------\\
        $sql2 = "SELECT email FROM cpa WHERE token = '$codigo'";
        $resultados2 = mysqli_query($conn, $sql);

        $rows = mysqli_fetch_assoc($resultados2);

        // Armazena o codigo e o email do utilizador que pode criar conta
        $_SESSION['codigoVerify'] = $codigo;
        $_SESSION['EmailCriarConta'] = $rows['email'];

//----------------------------------------------------QUERY 3----------------------------------------------------\\
        $sql3 = "DELETE FROM cpa WHERE token = '$codigo' AND email='".$email['email']."'";
        $resultado3 = mysqli_query($conn, $sql3);

        if(!$resultado3) {
            die("Erro ao atualizar: " . mysqli_error($conn)); // Verifica possíveis erros na atualização
        }
        else {
            // Redireciona para a página de verificação do Código
            header("Location: CriarConta.php");
            exit();
        }
    }else {
        // Se não houver dados submetidos, irá redirecionar para a página de verificação do Código
        header("Location: CodeVerifyPAGE.html");
        exit(); 
    }
?>
