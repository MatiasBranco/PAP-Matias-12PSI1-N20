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
                        <h5 class="card-title text-center mb-4">Recuperaração de Palavra-Passe</h5>
                        <form action="enviarEmailRecuperarPWD.php" method="POST">
                            <div class="mb-3">
                                <label for="email" class="form-label">Endereço e-mail *</label>
                                <input type="email" class="form-control" id="email" name="email" placeholder="Digite seu e-mail" required>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Enviar</button>
                            </div>
                            <br>
                            <div class="d-grid">
                                <button onclick="location.href = 'index.php';" class="btn btn-primary">Voltar</button>
                            </div>
                            <label style="color: red" class="form-label">Todos os campos com (*) são obrigatórios!</label>
                        </form>
                        <?php if (isset($_SESSION["ERROR3205"]) && $_SESSION["ERROR3205"] != ""): ?>
                            <div class="alert alert-danger mt-3" id="erro32" role="alert">
                                <?= $_SESSION["ERROR3205"]; ?>
                            </div>
                            <?php $_SESSION["ERROR3205"] = ""; ?>
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
