<?php
session_start();

if(!isset($_POST["inputGroupSelect01"]) && !isset($_POST["teste"]) && !isset($_POST["descricao"]) && !isset($_POST["datetime"])){
    header("Location: mi.php");
    exit();
}

include("ligacaoDB.php");

$sql="SELECT * FROM utilizadores WHERE email ='".$_SESSION['email']."'";
$resultado = mysqli_query($conn, $sql);

$rows = mysqli_fetch_assoc($resultado);

$ID_Utilizador = $rows["id"];


$sql1="INSERT INTO utilizadores_exames (id_utilizadores, id_motivo_incumprimento, desc_motivo, data_motivo) VALUES ($ID_Utilizador,". $_POST['inputGroupSelect01'].", '".$_POST["teste"]."', '".$_POST["descricao"]."', ".$_POST["datetime"].")"
?>