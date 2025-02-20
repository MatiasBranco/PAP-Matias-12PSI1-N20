<?php
    $servername = "localhost";
    $username = "root";
    $password = "";
    //$nameDB = "pessoal";

    //$conn = mysqli_connect($servername, $username, $password, $nameDB);
    $conn = mysqli_connect($servername, $username, $password);

    if(!$conn){
        die("Erro ao tentar fazer a conexão com a base de dados: ". mysqli_connect_error());
    }

    $escolheDB = mysqli_select_db($conn,"pap_database");

    if(!$escolheDB){
        echo "ERRO: Não foi possivel ter acesso á base de dados!";
        exit();
    }
?>
