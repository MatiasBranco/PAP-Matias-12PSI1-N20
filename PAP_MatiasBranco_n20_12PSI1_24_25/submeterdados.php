<?php
session_start(); // Inicia a sessão

// Verifica se o utilizador verificou o código
if (!isset($_SESSION['codigoVerify'])) {
    header("Location: CodeVerifyPAGE.html");
    exit();
}

$_codigoVerificacao = $_SESSION['codigoVerify'];

// Verifica se os dados foram enviados via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? null;
    $nome = $_POST['nome'] ?? null;
    $password = $_POST['password'] ?? null;
    $id_disciplina = $_POST['disciplina_select'] ?? null;

    // Verificação de campos obrigatórios
    if (!$email || !$nome || !$password || !$id_disciplina) {
        $_SESSION['codigoVerify'] = $_codigoVerificacao;
        header("Location: CriarConta.php?erro=1");
        exit();
    }

    include("ligacaoDB.php"); // Conexão à base de dados

    // Sanitização de entradas
    $email = mysqli_real_escape_string($conn, $email);
    $nome = mysqli_real_escape_string($conn, $nome);
    $password = mysqli_real_escape_string($conn, $password);
    $id_disciplina = (int)$id_disciplina;

    // Verifica se já existe um utilizador com o email fornecido
    $sql = "SELECT * FROM utilizadores WHERE email='$email'";
    $resultado1 = mysqli_query($conn, $sql);

    if (mysqli_num_rows($resultado1) == 0) {
        // Criação da senha com hash
        $pwd = hash('sha512', $password);

        // Insere o novo utilizador
        $sql1 = "INSERT INTO utilizadores (email, pwd, id_grupo_recrutamento, nome, tipo_ut, Ativo, Tema) 
                 VALUES ('$email', '$pwd', $id_disciplina, '$nome', 'Professor', 'Sim', 'Normal')";

        if (!mysqli_query($conn, $sql1)) {
            $_SESSION['codigoVerify'] = $_codigoVerificacao;
            header("Location: CriarConta.php?erro=1");
            exit();
        }

        // Recupera o ID do utilizador inserido
        $id_ut_ = mysqli_insert_id($conn);

        // Insere o utilizador na tabela de grupos
        $sql2 = "INSERT INTO grupos_recrutamento_has_utilizadores (grupos_recrutamento_id_grupo, utilizadores_id_utilizador) 
                 VALUES ($id_disciplina, $id_ut_)";

        if (!mysqli_query($conn, $sql2)) {
            // Remoção de registros parcialmente criados em caso de erro
            mysqli_query($conn, "DELETE FROM utilizadores WHERE id_utilizador=$id_ut_");
            $_SESSION['codigoVerify'] = $_codigoVerificacao;
            // Redireciona para a página inicial em caso de sucesso
            header("Location: index.php?erro=5");
            exit();
        }
    } else {
        // Se o email já estiver registrado
        $_SESSION['codigoVerify'] = $_codigoVerificacao;
        header("Location: CriarConta.php?erro=2");
        exit();
    }
} else {
    // Caso o método não seja POST, redireciona para a página de verificação do código
    header("Location: CodeVerifyPAGE.html");
    exit();
}
?>
