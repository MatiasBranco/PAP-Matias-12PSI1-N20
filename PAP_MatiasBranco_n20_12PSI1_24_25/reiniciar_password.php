<?php
    session_start();
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exame Vigio</title>
    <link rel="icon" type="image/x-icon" href="imgs/logo.png">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex justify-content-center align-items-center vh-100">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-4">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title text-center mb-4">Redefinir Palavra-Passe</h5>
                        <form action="update_password.php" method="POST">
                            <!-- Campo oculto para o token -->
                            <input type="hidden" name="token" id="token" value="<?php echo htmlspecialchars($_GET['token']); ?>">
                            
                            <!-- Campo para nova senha -->
                            <div class="mb-3">
                                <label for="password" class="form-label">Nova Palavra-Passe:</label>
                                <input type="password" class="form-control" id="password" name="password" placeholder="Escreva sua nova palavra passe" required>
                            </div>
                            
                            <!-- Botão de envio -->
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Redefinir Palavra-Passe</button>
                            </div>
                        </form>

                        <!-- Exibição de mensagens de erro -->
                        <?php if (isset($_SESSION["ERROR3204"]) && $_SESSION["ERROR3204"] != ""): ?>
                            <div class="alert alert-danger mt-3" id="erro322323" role="alert">
                                <?= $_SESSION["ERROR3204"]; ?>
                            </div>
                            <?php $_SESSION["ERROR3204"] = ""; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
