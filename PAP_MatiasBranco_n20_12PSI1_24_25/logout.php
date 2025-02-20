<?php
// Iniciar a sessão
session_start();

if(isset($_SESSION["email"]) && isset($_SESSION["Tipo_ut"])){
    // Destruir todas as variáveis de sessão
    session_unset();
    // Destruir a sessão
    session_destroy();
}
// Redirecionar para a página Login
header("Location: index.php");
exit();

?>
