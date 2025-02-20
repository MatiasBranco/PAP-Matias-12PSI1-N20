<?php
include("ligacaoDB.php");

header('Content-Type: application/json');

// Consulta para obter os eventos
$sql = "SELECT id, titulo AS title, data_inicio AS start, data_fim AS end, cor AS color FROM eventos";
$result = mysqli_query($conn, $sql);

$events = [];
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $events[] = $row;
    }
}

echo json_encode($events);

// Fecha a conexão
mysqli_close($conn);
