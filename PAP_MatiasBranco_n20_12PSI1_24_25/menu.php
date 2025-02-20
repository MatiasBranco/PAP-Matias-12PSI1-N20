<?php
session_start(); // Inicia a sessão

// Verifica se o utilizador está logado
if (!isset($_SESSION['email'])) {
    // Se o utilizador não estiver logado, redireciona para a página de login
    header("Location: index.php");
    exit();
}

include("ligacaoDB.php");

// Garantir que a consulta para o tema só é feita se o email existir na sessão
$email = $_SESSION['email'] ?? null;
if ($email) {
    $sqlVerificarTema = "SELECT Tema FROM utilizadores WHERE email = '$email'";
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

// Atualizar nome e tema do utilizador
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nome_utili'], $_POST['tema_select'])) {
    $nome_utili = mysqli_real_escape_string($conn, $_POST['nome_utili']);
    $tema_select = mysqli_real_escape_string($conn, $_POST['tema_select']);

    $sql = "UPDATE utilizadores SET nome='$nome_utili', Tema='$tema_select' WHERE email='$email'";
    $resultado = mysqli_query($conn, $sql);

    if (!$resultado) {
        $_SESSION["Erro_edit_dados_pessoais"] = "Ocorreu um erro ao editar o seu perfil! Tente novamente mais tarde!";

        $sqlEmail="SELECT * FROM utilizadores WHERE email='".$_SESSION["email"]."'";
        $result = mysqli_query($conn, $sqlEmail);
        $rows = mysqli_fetch_array($result);
    
        $sqlError2="INSERT INTO logs (id_pessoa, name_log, erro_203, data) VALUES (".$rows["id"].", 'ERROR: Menu', 'ERRO: O Utilizador tentou autualizar os seus dados no seu perfil! ".mysqli_error($conn)."', NOW())";
        mysqli_query($conn, $sqlError2);
    } else {
        $_SESSION["Erro_edit_dados_pessoais"] = "Os seus dados foram alterados com sucesso!";
    }
    header("Location: menu.php");
    exit();
}

// Verifique se o formulário foi submetido
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pwd_reset'], $_POST['pwd_atual'])) {
    $pwd_atual = mysqli_real_escape_string($conn, $_POST['pwd_atual']);
    $pwdd1 = mysqli_real_escape_string($conn, $_POST['pwd_reset']);

    // Hash da palavra-passe atual inserida
    $pwd_atual_hash = hash('sha512', $pwd_atual);

    // Verificar se a palavra-passe atual está correta
    $sqlVerificaPWD = "SELECT pwd FROM utilizadores WHERE email = '$email'";
    $resultadoPWD = mysqli_query($conn, $sqlVerificaPWD);
    $PASSWD_DB = ($resultadoPWD && $row = mysqli_fetch_assoc($resultadoPWD)) ? $row['pwd'] : '';

    if ($pwd_atual_hash !== $PASSWD_DB) {
        $_SESSION["Erro_edit_dados_pessoais"] = "Palavra-Passe atual incorreta!";
        header("Location: menu.php");
        exit();
    }
        
    // Validação de senha forte
    if (strlen($pwdd1) < 8 || !preg_match('/[A-Z]/', $pwdd1) || !preg_match('/[a-z]/', $pwdd1) || !preg_match('/\d/', $pwdd1)) {
        $_SESSION["Erro_edit_dados_pessoais"] = "A nova senha deve ter pelo menos 8 caracteres, uma letra maiúscula, uma minúscula e um número.";
        header("Location: menu.php");
        exit();
    }

    // Hash da nova palavra-passe e atualização no banco de dados
    $nova_pwd_hash = hash('sha512', $pwdd1);
    $sqlUpdatePWDD = "UPDATE utilizadores SET pwd = '$nova_pwd_hash' WHERE email = '$email'";

    if (mysqli_query($conn, $sqlUpdatePWDD)) {
        $_SESSION["Erro_edit_dados_pessoais"] = "Palavra-passe alterada com sucesso!";
        $_SESSION["enviaremail"] = "enviar";
        header("Location: enviaraviso.php");
        exit();
    } else {
        $_SESSION["Erro_edit_dados_pessoais"] = "Erro ao alterar a palavra-passe. Tente novamente.";
        header("Location: menu.php");
        exit();
    }
}
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['image'])) {
    $imageData = file_get_contents($_FILES['image']['tmp_name']);
    $imageData = mysqli_real_escape_string($conn, $imageData);
    $sql = "UPDATE utilizadores SET imagem='$imageData' WHERE email='".$email."'";
    mysqli_query($conn, $sql);
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exame Vigio</title>
    <link rel="icon" type="image/x-icon" href="imgs/logo.png">
    <link href="Styles_responce.css" rel="stylesheet">
    <link href="stylesMENU.css" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: <?php echo $bodyNavvy2 ?>;
        }
        .navbar-custom {
            background-color: <?php echo $bodyNavvy1 ?>;
        }
 
    </style>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
    <header class="text-center py-4">
        <h1>Bem-vindo ao Portal Exame Vigio</h1>
        <p>Sistema de gestão de horários para exames, destinado a professores e administradores</p>
    </header>
    <?php
        if (isset($_SESSION["Erro_edit_dados_pessoais"])) {
            $mensagemErro = $_SESSION["Erro_edit_dados_pessoais"];
            echo "<div id='popupErro' class='popup'>$mensagemErro</div>";
            unset($_SESSION["Erro_edit_dados_pessoais"]);
        }
    ?>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            let popup = document.getElementById("popupErro");
            if (popup) {
                popup.style.display = "block";
                setTimeout(() => {
                    popup.style.display = "none";
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
                    <li class="nav-item"><a class="nav-link active" href="menu.php">Home</a></li>
                    <?php if ($_SESSION['Tipo_ut'] == 'Admin'): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#" role="button" aria-expanded="false">Serviços</a>
                            <ul class="dropdown-menu dropdown-menu-dark" aria-labelledby="dropdownMenuButton2">
                                <li><a class="dropdown-item" href="#">🔔 Notificações</a></li>
                                <li><a class="dropdown-item" href="#" onclick="toggleDiv()">📅 Ver Horários para os Exames</a></li>
                                <li><a class="dropdown-item" href="mi.php">Motivos de Incumprimento</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="gerir_exames.php">Gerir Exames</a></li>
                                <li><a class="dropdown-item" href="gerir_profs.php">Gerir Professores</a></li>
                                <li><a class="dropdown-item" href="cgj.php">Criar Código de Junção</a></li>
                                <li><a class="dropdown-item" href="glf.php">Gerar Lista de Professores</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#" role="button" aria-expanded="false">Serviços</a>
                            <ul class="dropdown-menu dropdown-menu-dark" aria-labelledby="dropdownMenuButton2">
                                <li><a class="dropdown-item" href="#" onclick="toggleDiv()">📅 Ver Horários para os Exames</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="#">🔔 Notificações</a></li>
                                <li><a class="dropdown-item" href="mi.php">Motivos de Incumprimento</a></li>
                            </ul>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item"><a class="nav-link" href="#DefenicoesPopUp" data-bs-toggle="modal" data-bs-target="#DefenicoesPopUp">⚙️ Definições</a></li>
                    <li class="nav-item"><a class="nav-link" href="logout.php">Terminar Sessão</a></li>
                </ul>
            </div>
        </div>
    </nav>
    <hr>

    <div class="modal fade" id="DefenicoesPopUp" tabindex="-1" aria-labelledby="TituloDefenicoesPopUp" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="TituloDefenicoesPopUp">Definições da Conta</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <?php
                    if ($email) {
                        include("ligacaoDB.php");
                        
                        $email = mysqli_real_escape_string($conn, $email);
                        $sql = "SELECT * FROM utilizadores WHERE email = '$email'";
                        $resultado = mysqli_query($conn, $sql);
    
                        if ($resultado && mysqli_num_rows($resultado) > 0) {
                            $registo = mysqli_fetch_assoc($resultado);
                            $imageSrc = $registo['imagem'] ? 
                                'data:image/jpeg;base64,' . base64_encode($registo['imagem']) : 
                                'imgs/user_noimage.jpg';

                            echo '<form action="" method="POST" enctype="multipart/form-data">
                                    <label for="fileInput">
                                        <div class="image-container">
                                            <img id="profileImage" src="'.$imageSrc.'" alt="Foto de perfil">
                                            <div class="overlay">EDITAR</div>
                                        </div>
                                    </label>
                                    <input type="file" id="fileInput" name="image" accept="image/*" style="display: none;" onchange="previewImage(event)">
                                    <button type="submit" id="saveButton">Salvar</button>
                                </form>';
    

                            echo '
                            <form method="POST">
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email associado:</label>
                                    <input type="email" class="form-control" name="email" id="email" value="' . $email . '" readonly>
                                </div>
                                <div class="mb-3">
                                    <label for="nome_utili" class="form-label">Nome de Utilizador</label>
                                    <input type="text" class="form-control" id="nome_utili" name="nome_utili" value="' . $registo["nome"] . '">
                                </div>
                                <div class="mb-3">
                                    <label for="tema" class="form-label">Tema:</label>
                                    <select class="form-control" name="tema_select" id="tema_select">
                                        <option value="Escuro" ' . ($registo["Tema"] == "Escuro" ? "selected" : "") . '>Escuro</option>
                                        <option value="Normal" ' . ($registo["Tema"] == "Normal" ? "selected" : "") . '>Normal</option>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary">Guardar Alterações</button>
                                <a type="button" onclick="AtivateFORMResetPWD();" class="btn btn-primary">Redefenir Palavra-Passe</a>
                            </form>';
                        } else {
                            die("Erro: Dados do utilizador não encontrados.");
                        }

                        mysqli_close($conn);
                    }
                    ?>
                    <div id="visivelRESETpwd" name="visivelRESETpwd" style="visibility: hidden;">
                        <form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                            <div class="mb-3">
                                <label for="pwd_atual" class="form-label">Palavra-Passe Atual *</label>
                                <input type="password" class="form-control" id="pwd_atual" name="pwd_atual" required>
                            </div>
                            <div class="mb-3">
                                <label for="pwd_reset" class="form-label">Nova Palavra-Passe *</label>
                                <div style="position: relative;">
                                    <input type="password" class="form-control" id="pwd_reset" name="pwd_reset" required>
                                    <img id="toggleIcon" src="https://img.icons8.com/ios-filled/50/000000/invisible.png" 
                                        style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer; width: 30px; height: 30px;" 
                                        onclick="togglePassword()">
                                </div>
                                <small id="errorPASS" style="color: red;"></small>
                            </div>
                            <button type="button" class="btn btn-primary" onclick="validarSenha()">Verificar</button>
                            <button type="submit" class="btn btn-success" id="btnReset" style="visibility: hidden;">Redefinir Senha</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
               function previewImage(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById("profileImage").src = e.target.result;
                    document.getElementById("saveButton").style.display = "block";
                }
                reader.readAsDataURL(file);
            }
        }
        // Função de verificação em tempo real da palavra-passe
        function validarSenha() {
            var senha = document.getElementById("pwd_reset").value;
            var erro = document.getElementById("errorPASS");
            var botao = document.getElementById("btnReset");

            var regexMaiuscula = /[A-Z]/;
            var regexMinuscula = /[a-z]/;
            var regexNumero = /\d/;

            if (senha.length >= 8 && regexMaiuscula.test(senha) && regexMinuscula.test(senha) && regexNumero.test(senha)) {
                erro.textContent = "Senha válida!";
                erro.style.color = "green";
                botao.style.visibility = "visible";
            } else {
                erro.textContent = "A senha deve ter pelo menos 8 caracteres, uma letra maiúscula, uma minúscula e um número.";
                erro.style.color = "red";
                botao.style.visibility = "hidden";
            }
        }

        function togglePassword() {
            var input = document.getElementById("pwd_reset");
            var icon = document.getElementById("toggleIcon");

            if (input.type === "password") {
                input.type = "text";
                icon.src = "https://img.icons8.com/ios-filled/50/000000/visible.png";
            } else {
                input.type = "password";
                icon.src = "https://img.icons8.com/ios-filled/50/000000/invisible.png";
            }
        }

        // Função para alternar a visibilidade da senha
        function togglePassword() {
            var senhaInput = document.getElementById("pwd_reset");
            var olhoIcon = document.getElementById("toggleIcon");

            // Alterna o tipo de input (password ou text) e o ícone
            if (senhaInput.type === "password") {
                senhaInput.type = "text";
                olhoIcon.src = "https://img.icons8.com/ios-filled/50/000000/visible.png"; // Ícone de visível
            } else {
                senhaInput.type = "password";
                olhoIcon.src = "https://img.icons8.com/ios-filled/50/000000/invisible.png"; // Ícone de invisível
            }
        }
        function AtivateFORMResetPWD() {
            const formReset = document.getElementById("visivelRESETpwd");
            if (formReset.style.visibility === "hidden" || formReset.style.visibility === "") {
                formReset.style.visibility = "visible";
            } else {
                formReset.style.visibility = "hidden";
            }
        }
        function toggleDiv() {
            const formReset = document.getElementById("divcalendário");
            if (formReset.style.visibility === "hidden" || formReset.style.visibility === "") {
                formReset.style.visibility = "visible";
            } else {
                formReset.style.visibility = "hidden";
            }
        }
    </script>


</body>
</html>
<!--
<div id="divcalendário" style="visibility: hidden;">
        <div class="container mt-4">
            <h2 class="text-center mb-4">Calendário de Eventos</h2>
            Contêiner do calendário 
            <div id="calendario"></div>
        </div>

        <div class="modal fade" id="eventModal" tabindex="-1" aria-labelledby="eventModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="eventModalLabel">Detalhes do Evento</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p><strong>Título:</strong> <span id="eventoTitulo"></span></p>
                        <p><strong>Descrição:</strong> <span id="eventoDescricao"></span></p>
                        <p><strong>Início:</strong> <span id="eventoInicio"></span></p>
                        <p><strong>Fim:</strong> <span id="eventoFim"></span></p>
                    </div>
                </div>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

        <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var calendarEl = document.getElementById('calendario');

                var calendar = new FullCalendar.Calendar(calendarEl, {
                    initialView: 'dayGridMonth',
                    locale: 'pt', // defenir linguagem do Calendário para português
                    headerToolbar: {
                        left: 'prev,next',
                        center: 'title',
                        right: 'dayGridMonth,timeGridWeek'
                    },
                    events: 'buscar_eventos.php', // URL para ir buscar eventos via AJAX
                    eventClick: function (info) {
                        // Mostrar detalhes do evento no modal
                        document.getElementById('eventoTitulo').innerText = info.event.title;
                        document.getElementById('eventoDescricao').innerText = info.event.extendedProps.descricao || 'Não informado';
                        document.getElementById('eventoInicio').innerText = info.event.start.toLocaleString('pt');
                        document.getElementById('eventoFim').innerText = info.event.end
                            ? info.event.end.toLocaleString('pt')
                            : 'Não informado';

                        // Abrir modal
                        var eventModal = new bootstrap.Modal(document.getElementById('eventModal'));
                        eventModal.show();
                    },
                    eventColor: '#007bff', // Cor padrão para eventos
                    dayMaxEvents: true, // Compactar eventos
                });

                calendar.render();
            });
        </script>
    </div>
    -->