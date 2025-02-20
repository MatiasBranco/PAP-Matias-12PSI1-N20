<?php
session_start();
include("ligacaoDB.php");

// Verifica se o utilizador está logado e tem permissões de admin
if (!isset($_SESSION['email']) || $_SESSION['Tipo_ut'] != "Admin") {
    header("Location: menu.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['confirmar'])) {
    if (empty($_POST["coadjuvantes"])) {
        die("<div class='alert alert-danger'>Erro: Nenhum professor coadjuvante selecionado.</div>");
    }
    
    $coadjuvantes = isset($_POST["coadjuvantes"]) ? (array) $_POST["coadjuvantes"] : [];
    $data_exame = mysqli_real_escape_string($conn, $_POST['data_exame']);
    $hora_inicio = mysqli_real_escape_string($conn, $_POST['hora_inicio']);
    $hora_fim = mysqli_real_escape_string($conn, $_POST['hora_fim']);
    $num_vigilantes = intval($_POST['num_vigilantes']);
    
    if (!isset($_SESSION["DiscplinaExameEmCriacao"])) {
        die("<div class='alert alert-danger'>Erro: Nenhuma disciplina foi selecionada.</div>");
    }
    
    $disciplina = intval($_SESSION["DiscplinaExameEmCriacao"]);
    
    // Verifica se já existe um exame da mesma disciplina no mesmo dia e horário
    $queryVerificaExame = "SELECT id FROM exames 
                           WHERE data = '$data_exame' 
                           AND hora_inicio = '$hora_inicio' 
                           AND id_disciplina = $disciplina";
    $resultVerificaExame = mysqli_query($conn, $queryVerificaExame);

    if (mysqli_num_rows($resultVerificaExame) > 0) {
        die("<div class='alert alert-danger'>Erro: Já existe um exame desta disciplina neste horário.</div>");
    }
    
    // Obter grupos de recrutamento da disciplina
    $queryGrupos = "SELECT grupos_recrutamento_id_grupo FROM grupo_recrutamento_da_disciplina WHERE areas_exames_codigo = $disciplina";
    $resultGrupos = mysqli_query($conn, $queryGrupos);
    
    $gruposRecrutamento = [];
    while ($row = mysqli_fetch_assoc($resultGrupos)) {
        $gruposRecrutamento[] = $row['grupos_recrutamento_id_grupo'];
    }
    
    $gruposFiltrados = implode(',', array_map('intval', $gruposRecrutamento));

    // Verifica se os professores selecionados já estão ocupados em outro exame nesse horário
    $queryProfessoresOcupados = "SELECT DISTINCT ue.id_utilizadores 
                                 FROM utilizadores_exames ue
                                 JOIN exames e ON ue.id_exame = e.id
                                 WHERE e.data = '$data_exame'
                                 AND ('$hora_inicio' < e.hora_fim AND '$hora_fim' > e.hora_inicio)";
    $resultProfessoresOcupados = mysqli_query($conn, $queryProfessoresOcupados);
    $professoresOcupados = [];

    while ($row = mysqli_fetch_assoc($resultProfessoresOcupados)) {
        $professoresOcupados[] = $row['id_utilizadores'];
    }

    $idsProfessoresOcupados = implode(',', array_map('intval', $professoresOcupados));
    $condicaoProfessoresOcupados = !empty($idsProfessoresOcupados) ? "AND u.id NOT IN ($idsProfessoresOcupados)" : "";

    // Selecionar vigilantes disponíveis, excluindo os ocupados
    $queryVigilante = "SELECT u.id, u.email, COUNT(ue.id_utilizadores) AS num_vigilancias 
                       FROM utilizadores u
                       LEFT JOIN utilizadores_exames ue ON u.id = ue.id_utilizadores AND ue.Cargo = 'Vigilante'
                       WHERE u.tipo_ut='Professor' 
                       AND u.Ativo='Sim' 
                       AND u.id_grupo_recrutamento NOT IN ($gruposFiltrados) 
                       $condicaoProfessoresOcupados
                       GROUP BY u.id
                       ORDER BY num_vigilancias ASC, RAND()
                       LIMIT $num_vigilantes";

    $resultVigilante = mysqli_query($conn, $queryVigilante);
    $vigilantes = mysqli_fetch_all($resultVigilante, MYSQLI_ASSOC);
    
    // Se não houver vigilantes suficientes, cancelar a criação do exame
    if (count($vigilantes) < $num_vigilantes) {
        die("<div class='alert alert-danger'>Erro: Não há vigilantes suficientes disponíveis. Exame cancelado.</div>");
    }

    $_SESSION['vigilantes_selecionados'] = $vigilantes;
    $_SESSION['coadjuvantes_selecionados'] = $coadjuvantes;
    $_SESSION['dados_exame'] = [
        'data_exame' => $data_exame,
        'hora_inicio' => $hora_inicio,
        'hora_fim' => $hora_fim,
        'disciplina' => $disciplina
    ];
    
    echo "<link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>";
    echo "<div class='container mt-5'>";
    echo "<h3 class='mb-3'>Selecione Professores Substitutos</h3>";
    echo "<form method='POST' class='border p-4 rounded shadow'>";
    echo "<div class='mb-3'>";
    echo "<label for='substitutos' class='form-label'>Professores disponíveis:</label>";
    echo "<select class='form-select' name='substitutos[]' multiple>";
    foreach ($vigilantes as $vigilante) {
        echo "<option value='{$vigilante['id']}'>{$vigilante['email']} - {$vigilante['num_vigilancias']} vigilâncias</option>";
    }
    echo "</select>";
    echo "</div>";
    echo "<button type='submit' name='confirmar' value='1' class='btn btn-primary'>Confirmar</button>";
    echo "</form>";
    echo "</div>";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmar'])) {
    $dadosExame = $_SESSION['dados_exame'];
    $vigilantes = $_SESSION['vigilantes_selecionados'];
    $coadjuvantes = $_SESSION['coadjuvantes_selecionados'];
    $substitutos = isset($_POST['substitutos']) ? $_POST['substitutos'] : [];

    $queryInserirExame = "INSERT INTO exames (data, hora_inicio, hora_fim, id_disciplina) 
                          VALUES ('{$dadosExame['data_exame']}', '{$dadosExame['hora_inicio']}', '{$dadosExame['hora_fim']}', {$dadosExame['disciplina']})";
    mysqli_query($conn, $queryInserirExame);
    $ultimo_id_exame = mysqli_insert_id($conn);

    foreach ($coadjuvantes as $professor_id) {
        mysqli_query($conn, "INSERT INTO utilizadores_exames (id_utilizadores, id_exame, Cargo, estrutura) VALUES ($professor_id, $ultimo_id_exame, 'Coadjuvante', 'Vigilante')");
    }

    foreach ($vigilantes as $professor) {
        $professor_id = $professor['id'];
        $estrutura = in_array($professor_id, $substitutos) ? 'Substituto' : 'Vigilante';
        mysqli_query($conn, "INSERT INTO utilizadores_exames (id_utilizadores, id_exame, Cargo, estrutura) VALUES ($professor_id, $ultimo_id_exame, 'Vigilante', '$estrutura')");
    }
    
    $_SESSION["MSG"] = "Exame criado com sucesso!";
    header("Location: glf.php");
    exit();
}
?>
