<?php
session_start(); // Inicia a sessão
// Verifica se o utilizador está logado
if (!isset($_SESSION['email'])) {
    // Se o utilizador não estiver logado, redireciona para a página de login
    header("Location: index.php");
    exit();
}
if ($_SESSION['Tipo_ut'] != "Admin") {
    // Se o utilizador não estiver como admin, redireciona para a página de menu
    header("Location: menu.php");
    exit();
}
include("ligacaoDB.php");
// Garantir que a consulta para o tema só é feita se o email existir na sessão
$email = $_SESSION['email'] ?? null;
if ($email) {
    $emailEscaped = mysqli_real_escape_string($conn, $email); // Proteção contra SQL injection
    $sqlVerificarTema = "SELECT Tema FROM utilizadores WHERE email = '$emailEscaped'";
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
// Verifica se há um pedido POST para buscar professores
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["disciplina"])) {
    header('Content-Type: application/json');
    $disciplina = intval($_POST["disciplina"]);
    $_SESSION["DiscplinaExameEmCriacao"] = $disciplina;
    if (empty($disciplina)) {

        $sqlEmail="SELECT * FROM utilizadores WHERE email='$email'";
        $result = mysqli_query($conn, $sqlEmail);
        $rows = mysqli_fetch_array($result);

        $sqlError="INSERT INTO logs (id_pessoa, name_log, erro_203, data) VALUES (".$rows['id'].", 'Erro: Página GLF.php', 'A displina selecionada não foi salva corretamente. A tentativa foi feita pelo administrador: ".$rows["nome"]."', NOW()";
        mysqli_query($conn, $sqlError);

        echo json_encode(["error" => "Algo correu mal ao gerar a lista de professores coadjuvantes!"]);
        exit();
    }

    // Buscar os grupos de recrutamento da disciplina
    $sql1 = "SELECT grupos_recrutamento_id_grupo FROM grupo_recrutamento_da_disciplina WHERE areas_exames_codigo = $disciplina";
    $resultSQL1 = mysqli_query($conn, $sql1);
    
    $grupos_recrutamento_Coadjuvantes = [];
    while ($row = mysqli_fetch_assoc($resultSQL1)) {
        $grupos_recrutamento_Coadjuvantes[] = intval($row["grupos_recrutamento_id_grupo"]);
    }
    
    if (empty($grupos_recrutamento_Coadjuvantes)) {
        $sqlEmail="SELECT * FROM utilizadores WHERE email='$email'";
        $result = mysqli_query($conn, $sqlEmail);
        $rows = mysqli_fetch_array($result);

        $sqlError="INSERT INTO logs (id_pessoa, name_log, erro_203, data) VALUES (".$rows['id'].", 'Erro: Página GLF.php', 'Nenhum grupo de recrutamento foi encontrado para coadjuvar a disciplina selecionada não foi salva corretamente. A tentativa foi feita pelo administrador: ".$rows["nome"]."', NOW()";
        mysqli_query($conn, $sqlError);

        echo json_encode(["error" => "Nenhum grupo de recrutamento encontrado"]);
        exit();
    }
    
    $grupos_recrutamento_str = implode(",", $grupos_recrutamento_Coadjuvantes);
    
    // Buscar os professores dos grupos de recrutamento
    $sql2 = "SELECT id, nome, email, id_grupo_recrutamento FROM utilizadores WHERE id_grupo_recrutamento IN ($grupos_recrutamento_str) AND Ativo = 'Sim'";
    $resultSQL2 = mysqli_query($conn, $sql2);
    
    $professores = [];
    while ($row2 = mysqli_fetch_assoc($resultSQL2)) {
        $professores[] = [
            "id" => $row2["id"],
            "nome" => $row2["nome"],
            "email" => $row2["email"],
            "Grupo_Recrutamento" => $row2["id_grupo_recrutamento"]
        ];
    }
    
    echo json_encode(["professores" => $professores]);
    mysqli_close($conn);

    exit();
}

// Se não for um POST, renderiza a página normalmente
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exame Vigio</title>
    <link rel="icon" type="image/x-icon" href="imgs/logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function carregarProfessores() {
            let disciplinaSelecionada = document.getElementById("disciplina").value;
            if (!disciplinaSelecionada) {
                alert("Por favor, selecione uma disciplina antes de continuar.");
                return;
            }

            let formData = new FormData();
            formData.append("disciplina", disciplinaSelecionada);

            fetch("glf.php", {
                method: "POST",
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                let select = document.getElementById("coadjuvantes");
                select.innerHTML = "";

                if (data.error) {
                    alert(data.error);
                    select.innerHTML = "<option value='' disabled>Nenhum professor encontrado</option>";
                } else {
                    data.professores.forEach(professor => {
                        let option = document.createElement("option");
                        option.value = professor.id;
                        option.textContent = professor.nome + " (" + professor.email + ")";
                        select.appendChild(option);
                    });
                    let popupModal = new bootstrap.Modal(document.getElementById("popupForm"));
                    popupModal.show();
                }
            })
            .catch(error => console.error("Erro ao carregar professores:", error));
        }
    </script>
</head>
<body>
  <header class="text-center py-4">
        <h1>Portal Exame Vigio</h1>
        <p>Gere uma lista de professores para vigiar os exames!</p>
    </header>
    <?php
        if (isset($_SESSION["MSG"])) {
            $mensagemErro = $_SESSION["MSG"];
            echo "
            <div id='popupErro' class='alert alert-danger alert-dismissible fade show position-fixed top-0 end-0 m-3 shadow-lg' role='alert' style='z-index: 1050; max-width: 300px;'>
                <strong>Aviso!</strong> $mensagemErro
                <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Fechar'></button>
            </div>";
            unset($_SESSION["MSG"]);
        }
        $sqlEmail="SELECT * FROM utilizadores WHERE email='$email'";
        $result = mysqli_query($conn, $sqlEmail);
        $rows = mysqli_fetch_array($result);

        $sqlError="INSERT INTO logs (id_pessoa, name_log, erro_203, data) VALUES (".$rows['id'].", 'Erro: Página GLF.php', 'Erro: ".$_SESSION["MSG"]." |||A tentativa foi feita pelo administrador: ".$rows["nome"]."', NOW()";
        mysqli_query($conn, $sqlError);
    ?>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            let popup = document.getElementById("popupErro");
            if (popup) {
                setTimeout(() => {
                    let bsAlert = new bootstrap.Alert(popup);
                    bsAlert.close();
                }, 4000);
            }
        });
    </script>
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
    <div class="container-fluid mt-5">
        <div class="row align-items-center bg-light p-3 rounded">
            <label for="disciplina" class="form-label">Disciplina do Exame *</label>
            <select name="disciplina" class="form-select" id="disciplina" required>
                <option value="" disabled selected>Não Selecionada</option>
                <?php
                $sql = "SELECT * FROM disciplinas WHERE ativa = 1";
                $result = mysqli_query($conn, $sql);

                while ($row = mysqli_fetch_assoc($result)) {
                    echo "<option value='" . htmlspecialchars($row["codigo"]) . "'>" . htmlspecialchars($row["nome"]) . "</option>";
                }
                ?>
            </select>

            <button class="btn btn-success mt-3" onclick="carregarProfessores()">Gerar Lista</button>
        </div>
    </div>

    <div class="modal fade" id="popupForm" tabindex="-1" aria-labelledby="popupFormLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="popupFormLabel">Professores Disponíveis</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form action="gerarlistadeprofessoresparaexames.php" method="POST">
                        <label for="coadjuvantes23" class="form-label">Professores Coadjuvantes *</label>
                        <select name="coadjuvantes" id="coadjuvantes" class="form-select" multiple required>
                        <option value="" disabled>Selecione os professores</option>
                        </select>
                        <small class="text-muted">Segure CTRL para selecionar vários</small>
                        
                        <!-- Campo escondido para armazenar o número de opções selecionadas -->
                        <input type="hidden" id="num_professores_selecionados" name="num_professores_selecionados" value="0">
                            
                        <div class="col">
                            <label for="data_exame" class="form-label">Data do Exame *</label>
                            <input type="date" name="data_exame" class="form-control" id="data_exame" required>
                        </div>
                        <div class="col">
                            <label for="hora_inicio" class="form-label">Hora de Início *</label>
                            <input type="time" name="hora_inicio" class="form-control" id="hora_inicio" required>
                        </div>
                        <div class="col">
                            <label for="hora_fim" class="form-label">Hora de Fim *</label>
                            <input type="time" name="hora_fim" class="form-control" id="hora_fim" required>
                        </div>
                        <div class="col">
                            <label for="num_vigilantes" class="form-label">Nº de Professores Vigilantes *</label>
                            <input type="number" name="num_vigilantes" class="form-control" id="num_vigilantes" placeholder="0" required>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-success">Confirmar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <form method="POST" action="tirarDisp.php">
    <div class="container-fluid mt-5">
        <div class="row align-items-center bg-light p-3 rounded">
            <div class="col">
                <label for="professor_id" class="form-label">Professores Disponiveis para o Exame *</label>
                <select name="professor_id" class="form-select" id="professor_id" >
                    <option value="" disabled selected>Não Selecionada</option>
                    <?php
                    
                    include("ligacaoDB.php");

                    $sql = "SELECT u.*
                    FROM utilizadores u WHERE u.id NOT IN (SELECT ute.id_utilizador FROM utilizadores_to_exames ute) AND u.tipo_ut = 'Professor'AND u.Ativo = 'Sim';";
                    $result = mysqli_query($conn, $sql);

                    if ($result && mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            echo "<option value='" . htmlspecialchars($row["id"]) . "'>" . htmlspecialchars($row["email"]) ." - ". htmlspecialchars($row["nome"]) . "</option>";
                        }
                    } else {
                        echo "<option value=''>Nenhuma professor disponivel encontrado</option>";
                    }

                    mysqli_close($conn);
                    ?>
                </select>
            </div>
            <div class="col">
                <label for="hora_fim" class="form-label" required>Até ao Dia*</label>
                <input type="datetime-local" name="horario" class="form-control" id="horario" required>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-success mt-4">Tirar Disponiblidade Para o Próximo Exame Criado</button>
            </div>
            <label for="num_vigilantes" class="form-label" style="color: green; font-size: 15px;">
                <?php
                    if (isset($_SESSION["alert333"])) {
                        echo $_SESSION["alert333"];
                        // Aguardar 5 segundos antes de destruir a variável da sessão
                        sleep(5);
                        unset($_SESSION["alert333"]);
                        echo "";
                    }
                ?>
            </label>
            <label for="num_vigilantes" class="form-label" style="color: red;">(*) preenchimento do campo obrigatório</label>
        </div>
    </div>
    </form>


    <form method="POST" action="tirarInDisp.php">
    <div class="container-fluid mt-5">
        <div class="row align-items-center bg-light p-3 rounded">
            <div class="col">
                <label for="professor_id" class="form-label">Professores Indisponiveis para o Exame *</label>
                <select name="professor_id" class="form-select" id="professor_id" >
                    <option value="" disabled selected>Não Selecionada</option>
                    <?php
                    include("ligacaoDB.php");

                    $sql = "SELECT u.* FROM utilizadores u WHERE u.id IN (SELECT ute.id_utilizador FROM utilizadores_to_exames ute) AND u.tipo_ut = 'Professor' AND u.Ativo = 'Sim';";
                    $result = mysqli_query($conn, $sql);

                    if ($result && mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            echo "<option value='" . htmlspecialchars($row["id"]) . "'>" . htmlspecialchars($row["email"]) ." - ". htmlspecialchars($row["nome"]) . "</option>";
                        }
                    } else {
                        echo "<option value=''>Nenhuma professor indesponivel encontrado!</option>";
                    }

                    mysqli_close($conn);
                    ?>
                </select>
            </div>

            <div class="col-auto">
                <button type="submit" class="btn btn-success mt-4">Tirar Indisponiblidade Para o Próximo Exame Criado</button>
            </div>
            <label for="num_vigilantes" class="form-label" style="color: green; size: 15px;">
                <?php 
                    if(isset($_SESSION["alert533"])){ 
                        echo $_SESSION["alert533"]; 
                        // Aguardar 5 segundos antes de destruir a variável da sessão
                        sleep(5);
                        unset($_SESSION["alert533"]); 
                        echo "";
                    } 
                        
                ?>
            </label>    
            <label for="num_vigilantes" class="form-label" style="color: red;">(*) preenchimento do campo obrigatório</label>
        </div>
    </div>
    </form>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>