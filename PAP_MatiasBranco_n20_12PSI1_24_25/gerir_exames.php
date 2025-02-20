<?php
session_start(); // Inicia a sessão

// Verifica se o utilizador está logado
if (!isset($_SESSION['email'])) {
    header("Location: index.php");
    exit();
}

// Verifica se o utilizador é admin
if (!isset($_SESSION['Tipo_ut']) || $_SESSION['Tipo_ut'] !== "Admin") {
    header("Location: menu.php");
    exit();
}

include("ligacaoDB.php");

// Inicializa variáveis
$email = $_SESSION['email'] ?? null;
$tema = "Normal"; // Tema padrão

// Obtém o tema do utilizador de forma segura
if ($email) {
    $sqlVerificarTema = "SELECT Tema FROM utilizadores WHERE email = ?";
    $stmt = mysqli_prepare($conn, $sqlVerificarTema);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $resultTema = mysqli_stmt_get_result($stmt);

    if ($resultTema && $row = mysqli_fetch_assoc($resultTema)) {
        $tema = $row["Tema"] ?: "Normal"; // Garante um valor padrão
    }
    mysqli_stmt_close($stmt);
}

// Definir cores com base no tema
$bodyNavvy1 = ($tema === "Normal") ? "#343a40" : "#1C1C1C";
$bodyNavvy2 = ($tema === "Normal") ? "#f5f5f9" : "#343a40";

// Consulta os exames e professores responsáveis
$sql = "SELECT 
            e.id AS exame_id, e.data, e.hora_inicio, e.hora_fim, d.nome AS disciplina,
            u.id AS utilizador_id, u.nome AS utilizador_nome, u.email,
            ue.Cargo, ue.Estrutura, ue.data_motivo, 
            ue.id_motivo_incumprimento, mi.descricao AS motivo_incumprimento
        FROM exames e
        JOIN disciplinas d ON e.id_disciplina = d.codigo
        LEFT JOIN utilizadores_exames ue ON e.id = ue.id_exame
        LEFT JOIN utilizadores u ON ue.id_utilizadores = u.id
        LEFT JOIN motivos_incumprimento mi ON ue.id_motivo_incumprimento = mi.ID
        ORDER BY e.data, e.hora_inicio, e.id";

$result = mysqli_query($conn, $sql);

$exames = [];
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $exame_id = $row['exame_id'];

        if (!isset($exames[$exame_id])) {
            $exames[$exame_id] = [
                "data" => $row["data"],
                "hora_inicio" => $row["hora_inicio"],
                "hora_fim" => $row["hora_fim"],
                "disciplina" => $row["disciplina"],
                "utilizadores" => []
            ];
        }

        if ($row['utilizador_id']) {
            $motivo = !empty($row["motivo_incumprimento"]) ? "{$row['motivo_incumprimento']} - [{$row['data_motivo']}]" : "N/A";
            $exames[$exame_id]["utilizadores"][] = [
                "nome" => $row["utilizador_nome"],
                "email" => $row["email"],
                "cargo" => $row["Cargo"] ?: "N/A",
                "estrutura" => $row["Estrutura"] ?: "N/A",
                "motivo_incumprimento" => $motivo
            ];
        }
    }
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exame Vigio</title>
    <link rel="icon" type="image/x-icon" href="imgs/logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: <?= htmlspecialchars($bodyNavvy2, ENT_QUOTES, 'UTF-8') ?>; }
        .navbar-custom { background-color: <?= htmlspecialchars($bodyNavvy1, ENT_QUOTES, 'UTF-8') ?>; }
        .navbar-custom .navbar-brand, .navbar-custom .nav-link { color: #ffffff !important; }
        .navbar-custom .nav-link:hover { color: #f8f9fa !important; }
        .navbar-custom .dropdown-menu { background-color: #495057; }
        .navbar-custom .dropdown-item:hover { background-color: #6c757d; }
        .navbar-toggler { border: none; background-color: transparent; outline: none; }
        .navbar-toggler-icon {
            background-image: url("imgs/iconMobile.png");
            background-size: contain;
            background-repeat: no-repeat;
            filter: invert(1) brightness(100%);
            width: 24px;
            height: 24px;
        }
        header { background-color: #007bff; color: white; padding: 20px 0; }
        .modal-header { background-color: #007bff; color: white; }
        .modal-footer { background-color: #f8f9fa; }
        .circle {
            background-color: #aaa;
            border-radius: 50%;
            width: 150px;
            height: 150px;
            overflow: hidden;
            position: relative;
            display: flex;
            justify-content: center;
            margin: auto;
        }
        .circle img { width: 100%; height: 100%; object-fit: cover; }
        .modal-body { font-size: 16px; }
        @media (max-width: 768px) {
            .fc-toolbar h2 { font-size: 1rem; }
            .fc-daygrid-day-number { font-size: 0.9rem; }
        }
    </style>
</head>
<body>
    <header class="text-center py-4">
        <h1>Gestão de Exames</h1>
        <p>Aqui pode observar todos os exames marcados e editá-los!</p>
    </header>

    <?php if (isset($_SESSION["MSG"])): ?>
        <div id="popupErro" class="alert alert-danger alert-dismissible fade show position-fixed top-0 end-0 m-3 shadow-lg" role="alert" style="z-index: 1050; max-width: 300px;">
            <strong>Aviso!</strong> <?= htmlspecialchars($_SESSION["MSG"]) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
        <?php unset($_SESSION["MSG"]); ?>
    <?php endif; ?>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            let popup = document.getElementById("popupErro");
            if (popup) {
                setTimeout(() => new bootstrap.Alert(popup).close(), 4000);
            }
        });
    </script>

    <nav class="navbar navbar-expand-lg navbar-custom">
        <div class="container-fluid">
            <a class="navbar-brand" href="gerir_exames.php">Exame Vigio</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link active" href="menu.php">Voltar</a></li>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container mt-4">
        <?php foreach ($exames as $exame_id => $exame): ?>
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Exame ID: <?= htmlspecialchars($exame_id) ?> - <?= htmlspecialchars($exame["disciplina"]) ?></h5>
                    <small>📅 <?= htmlspecialchars($exame["data"]) ?> | 🕒 <?= htmlspecialchars($exame["hora_inicio"]) ?> - <?= htmlspecialchars($exame["hora_fim"]) ?></small>
                </div>
                <div class="card-body">
                    <table class="table table-striped">
                        <thead class="table-dark">
                            <tr>
                                <th>Nome</th>
                                <th>Email</th>
                                <th>Cargo</th>
                                <th>Estrutura</th>
                                <th>Motivo de Incumprimento</th>
                                <th>Remover e Gerar Outro Professor</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($exame["utilizadores"] as $utilizador): ?>
                                <tr>
                                    <td><?= htmlspecialchars($utilizador["nome"]) ?></td>
                                    <td><?= htmlspecialchars($utilizador["email"]) ?></td>
                                    <td><?= htmlspecialchars($utilizador["cargo"]) ?></td>
                                    <td><?= htmlspecialchars($utilizador["estrutura"]) ?></td>
                                    <td><?= htmlspecialchars($utilizador["motivo_incumprimento"]) ?></td>
                                    <td><button class="btn btn-sm btn-secondary">Gerar Outro Professor</button></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
