<?php
// Inicia a sessão para verificar o login do utilizador
session_start();
if (!isset($_SESSION['email'])) {
    // Redireciona para a página inicial se o utilizador não estiver logado
    header("Location: index.php");
    exit();
}
if($_SESSION['Tipo_ut'] != "Admin"){
    header("Location: menu.php");
    exit();
}

// Inclui o arquivo de conexão com o banco de dados
include("ligacaoDB.php");

// Obtém o tema do utilizador com base no e-mail armazenado na sessão
$email = mysqli_real_escape_string($conn, $_SESSION['email']);
$sqlVerificarTema = "SELECT Tema FROM utilizadores WHERE email = '$email'";
$resultadosVerificarTema = mysqli_query($conn, $sqlVerificarTema);
$tema = ($resultadosVerificarTema && $row = mysqli_fetch_assoc($resultadosVerificarTema)) ? $row['Tema'] : 'Normal';

// Define as cores da interface com base no tema selecionado
$bodyNavvy1 = ($tema === 'Normal') ? "#f5f5f9" : "#1C1C1C";  // Cor de fundo
$h1Navvy1 = ($tema === 'Normal') ? "preto" : "#FFF";  // Cor do título

// Função para buscar resultados no banco de dados com base nos filtros
function fetchResults($conn, $filtro, $pesquisa) {
    $pesquisa = mysqli_real_escape_string($conn, $pesquisa);
    $query = "SELECT * FROM utilizadores";

    // Adiciona filtros à query com base no critério selecionado
    if ($filtro == "1") {
        $query .= " WHERE email LIKE '%$pesquisa%'";
    } elseif ($filtro == "2") {
        $query .= " WHERE nome LIKE '%$pesquisa%'";
    } elseif ($filtro == "3") {
        $query .= " WHERE id_grupo_recrutamento LIKE '%$pesquisa%'";
    }

    // Executa a consulta e retorna os resultados
    return mysqli_query($conn, $query);
}

// Verifica se um ID de utilizador foi passado para edição
$selectedUser = null;
if (isset($_GET['edit_user'])) {
    $userId = mysqli_real_escape_string($conn, $_GET['edit_user']);
    $sqlUser = "SELECT * FROM utilizadores WHERE email = '$userId'";
    $resultUser = mysqli_query($conn, $sqlUser);

    if ($resultUser && $selectedUser = mysqli_fetch_assoc($resultUser)) {
        // Dados do utilizador carregados para o modal
    }
}

// Atualiza os dados do utilizador se um formulário for enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $nome = mysqli_real_escape_string($conn, $_POST['editNome']);
    $tipo_ut = mysqli_real_escape_string($conn, $_POST['tipo_ut']);
    $ativo = mysqli_real_escape_string($conn, $_POST['Ativo']);

    // Query para atualizar o utilizador no banco de dados
    $sqlUpdate = "UPDATE utilizadores 
                  SET nome = '$nome', tipo_ut = '$tipo_ut', Ativo = '$ativo' WHERE email = '$email'";

    if (mysqli_query($conn, $sqlUpdate)) {
        echo "<div class='alert alert-success' role='alert'>Utilizador atualizado com sucesso!</div>";

        $sqlEmail="SELECT * FROM utilizadores WHERE email='".$_SESSION["email"]."'";
        $result = mysqli_query($conn, $sqlEmail);
        $rows = mysqli_fetch_array($result);
    
        $sqlError2="INSERT INTO logs (id_pessoa, name_log, erro_203, data) VALUES (".$rows["id"].", 'Sucesso: Gerir Professores', 'Sucesso: Utilizador autalizado com sucesso pelo administrador:  ".$rows["nome"]."', NOW())";
        mysqli_query($conn, $sqlError2);
    } else {
        echo "<div class='alert alert-danger' role='alert'>Erro ao atualizar o utilizador: " . mysqli_error($conn) . "</div>";

        $sqlEmail="SELECT * FROM utilizadores WHERE email='$email'";
        $result = mysqli_query($conn, $sqlEmail);
        $rows = mysqli_fetch_array($result);

        $sqlError="INSERT INTO logs (id_pessoa, name_log, erro_203, data) VALUES (".$rows['id'].", 'Erro: Gerir professores', '".mysqli_error($conn)."|||| Alterações feitas pelo administrador: ".$rows["nome"]."', NOW()";
        mysqli_query($conn, $sqlError);
    }
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exame Vigio</title>
    <link rel="icon" type="image/x-icon" href="imgs/logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        body {
            background-color: <?php echo $bodyNavvy1; ?>;
        }
        h1 {
            color: <?php echo $h1Navvy1; ?>;
        }
        .results-container {
            margin-top: 20px;
        }
        .navbar-custom {
            background-color: <?php echo $bodyNavvy1 ?>;
        }
        .navbar-custom .navbar-brand,
        .navbar-custom .nav-link {
            color: #ffffff !important;
        }
        header {
            background-color: #007bff;
            color: white;
            padding: 20px 0;
        }
    </style>
</head>
<body>
    <header class="text-center py-4">
        <h1>Portal Exame Vigio</h1>
        <p>Sistema de gestão de professores e administradores</p>
    </header>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand text-white" href="#">Exame Vigio</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <form class="d-flex ms-auto" id="searchForm" method="GET">
                    <select name="select23" class="form-select me-2" id="filterSelect">
                        <option value="0">Sem Filtros</option>
                        <option value="1">Por E-mail</option>
                        <option value="2">Por Nome</option>
                        <option value="3">Por ID Disciplina</option>
                    </select>
                    <input class="form-control me-2" type="text" name="pesquisa" id="searchInput" placeholder="Pesquisar">
                    <button class="btn btn-light" type="submit">Pesquisar</button>
                </form>
                <button class="btn btn-success ms-2" onclick="location.href = 'menu.php';">Menu</button>
            </div>
        </div>
    </nav>

    <!-- Exibindo resultados de pesquisa -->
    <div class="container-fluid results-container">
        <div id="alertContainer"></div>
        <div id="resultsTable">
            <?php 
            $filtro = $_GET['select23'] ?? "0";
            $pesquisa = $_GET['pesquisa'] ?? "";
            $resultado = fetchResults($conn, $filtro, $pesquisa);

            if ($resultado && mysqli_num_rows($resultado) > 0) {
                echo '<table class="table table-bordered table-responsive">
                    <thead>
                        <tr>
                            <th>E-mail</th>
                            <th>Nome</th>
                            <th>Tipo</th>
                            <th>ID Grupo Recrutamento</th>
                            <th>Ativo</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>';
                
                while ($row = mysqli_fetch_assoc($resultado)) {
                    echo '<tr>
                        <td>' . htmlspecialchars($row['email']) . '</td>
                        <td>' . htmlspecialchars($row['nome']) . '</td>
                        <td>' . htmlspecialchars($row['tipo_ut']) . '</td>
                        <td>' . htmlspecialchars($row['id_grupo_recrutamento']) . '</td>
                        <td>' . htmlspecialchars($row['Ativo']) . '</td>
                                  <td>';
                        
                    // Impede que o utilizador logado edite os seus próprios dados
                    if ($row['email'] !== $_SESSION['email']) {
                        echo '<a href="?edit_user=' . htmlspecialchars($row['email']) . '" class="btn btn-sm btn-primary">Editar</a>';
                    } else {
                        echo '<button class="btn btn-sm btn-secondary" disabled>Editar</button>';
                    }

                    echo '</td>
                    </tr>';
                }
                echo '</tbody></table>';
            } else {
                echo '<p class="text-muted">Nenhum resultado encontrado.</p>';
                $sqlError="INSERT INTO logs (name_log, erro_203, data) VALUES ('Erro: Gerir professores', '".mysqli_error($conn)."|||| Erro ao obter utilizadores da base de dados!', NOW()";
                mysqli_query($conn, $sqlError);
            }
            ?>
        </div>
    </div>

    <!-- Modal de Edição -->
    <?php if ($selectedUser): ?>
    <div class="modal fade show" id="editModal" tabindex="-1" style="display: block;" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Editar utilizador</h5>
                </div>
                <div class="modal-body">
                    <form method="POST">
                        <div class="mb-3">
                            <label for="editEmail" class="form-label">E-mail</label>
                            <input type="email" class="form-control" id="editEmail" name="email" value="<?php echo htmlspecialchars($selectedUser['email']); ?>" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="editNome" class="form-label">Nome</label>
                            <input type="text" class="form-control" id="editNome" name="editNome" value="<?php echo htmlspecialchars($selectedUser['nome']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="editTipo" class="form-label">Tipo</label>
                            <select class="form-select" id="editTipo" name="tipo_ut">
                                <option value="Admin" <?php echo ($selectedUser['tipo_ut'] === 'Admin') ? 'selected' : ''; ?>>Admin</option>
                                <option value="Professor" <?php echo ($selectedUser['tipo_ut'] === 'Professor') ? 'selected' : ''; ?>>Professor</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="editTipo" class="form-label">Ativo?</label>
                            <select class="form-select" id="editAtivo" name="Ativo">
                                <option value="Sim" <?php echo ($selectedUser['Ativo'] === 'Sim') ? 'selected' : ''; ?>>Sim</option>
                                <option value="Nao" <?php echo ($selectedUser['Ativo'] === 'Nao') ? 'selected' : ''; ?>>Não</option>
                            </select>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" onclick="window.location='?';">Fechar</button>
                            <button type="submit" class="btn btn-primary">Salvar alterações</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</body>
</html>
