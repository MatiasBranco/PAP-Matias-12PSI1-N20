<?php
session_start(); // Inicia a sessão

// Verifica se o utilizador está logado
if (!isset($_SESSION['email'])) {
    // Se o utilizador não estiver logado, redireciona para a página de login
    header("Location: index.php");
    exit();
}

include("ligacaoDB.php");

// Garantir que a consulta para o tema só é feita se o email existir na sessão
$email = $_SESSION['email'] ?? null;
if ($email) {
    $email = mysqli_real_escape_string($conn, $email); // Sanitiza o email para evitar SQL Injection
    $sqlVerificarTema = "SELECT Tema FROM utilizadores WHERE email = '$email'";
    $resultadosVerificarTema = mysqli_query($conn, $sqlVerificarTema);

    // Verifica se a consulta retornou um resultado válido
    if ($resultadosVerificarTema && $row = mysqli_fetch_assoc($resultadosVerificarTema)) {
        $tema = $row["Tema"];
    } else {
        $tema = "Normal"; // Valor padrão se o tema não for encontrado
    }

    // Define as cores com base no tema
    if ($tema == "Normal") {
        $bodyNavvy1 = "#343a40";
        $bodyNavvy2 = "#f5f5f9";
    } else {
        $bodyNavvy1 = "#1C1C1C";
        $bodyNavvy2 = "#343a40";
    }
} else {
    // Caso o email não exista, redireciona para o login
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exame Vigio</title>
    <link rel="icon" type="image/x-icon" href="imgs/logo.png">
    <link href="Styles_responce.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background-color: <?php echo $bodyNavvy2 ?>;
        }
        .navbar-custom {
            background-color: <?php echo $bodyNavvy1 ?>;
        }
        .navbar-custom .navbar-brand,
        .navbar-custom .nav-link {
            color: #ffffff !important;
        }
        .navbar-custom .nav-link:hover {
            color: #f8f9fa !important;
        }
        .navbar-custom .dropdown-menu {
            background-color: #495057;
        }
        .navbar-custom .dropdown-item:hover {
            background-color: #6c757d;
        }
        .navbar-toggler {
            border: none;
            background-color: transparent;
            outline: none;
        }
        header {
            background-color: #007bff;
            color: white;
            padding: 20px 0;
        }
        .modal-header {
            background-color: #007bff;
            color: white;
        }
        .modal-footer {
            background-color: #f8f9fa;
        }
    </style>
</head>
<body>
    <header class="text-center py-4">
        <h1>Portal Exame Vigio</h1>
        <p>Caso tenha um motivo de acordo com a Norma nº2 do artigo da JNE do presente ano. Deverá indicar aqui antes dos exames para os administradores poderem avaliar a situação.</p>
    </header>

    <nav class="navbar navbar-expand-lg navbar-custom">
        <div class="container-fluid">
            <a class="navbar-brand" href="menu.php">Exame Vigio</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="menu.php">Voltar</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <h2 class="text-center mb-4">Adicionar Motivo de Incumprimento</h2>
        <form action="processar_motivo.php" method="POST" class="p-4 shadow-sm rounded" style="background-color: #ffffff;">
            <div class="input-group mb-3">
                <label class="input-group-text" for="inputGroupSelect01">Motivo</label>
                <select class="form-select" id="inputGroupSelect01" onchange="document.getElementById('teste').textContent = this.value">
                    <option selected>Sem seleção...</option>
                    <?php
                    include("ligacaoDB.php"); // Conexão à base de dados

                    // Query para ver a tabela motivos_incumprimento
                    $sql = "SELECT * FROM motivos_incumprimento";

                    // Executar query
                    if ($result = mysqli_query($conn, $sql)) {
                        if (mysqli_num_rows($result) > 0) {
                            // Loop para mostrar todas as opções
                            while ($row = mysqli_fetch_assoc($result)) {
                                echo "<option value='" . htmlspecialchars($row["ajuda"]) . "'>" . htmlspecialchars($row["descricao"]) . "</option>";
                            }
                        } else {
                            echo "<option value=''>Nenhum motivo encontrado</option>";
                        }
                        mysqli_free_result($result);
                    } else {
                        echo "<option value=''>Erro ao carregar motivos</option>";
                    }

                    // Fechar conexão à base de dados
                    mysqli_close($conn);
                    ?>
                </select>
            </div>
            <label for="ajuda" class="form-label">Ajuda referente à escolha anterior *</label>
            <label class="input-group-text" id="teste" style="word-wrap: break-word; white-space: normal; display: block; width: 100%; text-align: left; color: red">Sem seleção...</label>
            <div class="mb-3">
                <label for="descricao" class="form-label">Faça uma breve descrição  *</label>
                <textarea class="form-control" id="descricao" name="descricao" rows="4" placeholder="Descreva o motivo de incumprimento" required></textarea>
            </div>
            <div class="mb-3" hidden>
                <input type="datetime-local" id="datetime">
            </div>

            <button type="submit" class="btn btn-primary w-100">Submeter Motivo</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Função para formatar a data e hora para o formato do input
    function formatDateForInput(date) {
      const year = date.getFullYear();
      const month = String(date.getMonth() + 1).padStart(2, '0'); // Mês começa em 0
      const day = String(date.getDate()).padStart(2, '0');
      const hours = String(date.getHours()).padStart(2, '0');
      const minutes = String(date.getMinutes()).padStart(2, '0');

      // Retorna no formato 'YYYY-MM-DDTHH:MM'
      return `${year}-${month}-${day}T${hours}:${minutes}`;
    }

    // Pegando o elemento input
    const dateTimeInput = document.getElementById('datetime');

    // Pegando a data e hora atuais
    const now = new Date();

    // Formatando e configurando o valor do input
    dateTimeInput.value = formatDateForInput(now);
  </script> 
</body>
</html>
