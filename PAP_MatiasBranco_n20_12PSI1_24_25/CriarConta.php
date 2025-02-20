<?php
session_start(); // Inicia a sessão

// Verifica se o utilizador verificou o código
if (!isset($_SESSION['codigoVerify'])) {
    // Se o utilizador não tiver colocado o codigo irá redirecionar para a página de Verificação do Código
    header("Location: CodeVerifyPAGE.html");
    exit();
}

?>
<!DOCTYPE html>
<html lang="PT">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exame Vigio</title>
    <link rel="icon" type="image/x-icon" href="imgs/logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header text-center">
                        <h2>Formulário de Registo</h2>
                    </div>
                    <!-- Formulário de preencher os dados -->
                    <div class="card-body">
                        <form name="formCriarConta" method="POST" action="submeterdados.php">
                            <div id="erroX" class="text-danger"></div>
                            <!-- Nome -->
                            <div class="mb-3">
                                <label for="nome" class="form-label">Nome de Professor *</label>
                                <input type="text" class="form-control" id="nome" name="nome"
                                    placeholder="Escreva aqui o seu nome completo">
                                <div id="erro1" style="color: red;"></div>
                            </div>

                            <!-- Email -->
                            <div class="mb-3">
                                <label for="email" class="form-label">Email *</label>
                                <input type="text" class="form-control" id="email" name="email"
                                    value="<?php echo $_SESSION['EmailCriarConta']; ?>" readonly>
                                <div id="erro2" style="color: red;"></div>
                            </div>


                            <!-- Senha -->
                            <div class="mb-3">
                                <label for="senha" class="form-label">Palavra-passe: *</label>
                                <input type="password" class="form-control" id="password" name="password"
                                    placeholder="Escreva sua palavra-passe">
                                <div id="erro3" style="color: red;"></div>
                            </div>

                            <!-- Confirmar Senha -->
                            <div class="mb-3">
                                <label for="confirmarSenha" class="form-label">Confirmar Senha *</label>
                                <input type="password" class="form-control" id="confirmarPassword" name="confirmarPassword"
                                    placeholder="Confirme sua palavra-passe">
                                <div id="erro4" style="color: red;"></div>
                            </div>

                            <!-- Escolher Disciplina -->
                            <div class="mb-3">
                                <label for="disciplina" class="form-label">Disciplina do Professor *</label>
                                <select name="disciplina_select" id="disciplina_select" class="form-control">
                                    <?php
                                    include("ligacaoDB.php"); // Conexão a base de dados
                                    
                                    // Query para ver a tabela disciplinas
                                    $sql = "SELECT * FROM grupos_recrutamento";

                                    // Executar query
                                    if ($result = mysqli_query($conn, $sql)) {

                                        // Ver se existe resultados
                                        if (mysqli_num_rows($result) > 0) {
                                            // Loop/Ciclo para mostrar todas as disciplinas existentes num SELECT 
                                            while ($row = $result->fetch_assoc()) {
                                                echo "<option value='" . htmlspecialchars($row["id"]) . "'>" . htmlspecialchars($row["nome"]) . "</option>";
                                            }
                                        } else {
                                            // Caso não Exista Disciplinas
                                            echo "<option value=''>Nenhuma disciplina encontrada</option>";
                                        }

                                        // Libera a memória utilizada pelos resultados da consulta, melhorando a eficiência e evitando o consumo desnecessário de recursos
                                        mysqli_free_result($result);

                                    } else {
                                        // Se a query falhar
                                        echo "<option value=''>Erro ao carregar disciplinas</option>";
                                        $rows = mysqli_fetch_array($result);
                                        $sqlError="INSERT INTO logs (name_log, erro_203, data) VALUES ('Erro carregar Discplinas na página CriarConta', '".mysqli_error($conn)."', NOW()";
                                        mysqli_query($conn, $sqlError);
                                    }

                                    // Fechar Conexão á base de dados
                                    mysqli_close($conn);
                                    ?>
                                </select>
                                <div id="erro5" style="color: red;"></div>
                            </div>
                            </br>
                            <!-- Botão confirmar dados -->
                            <input type="button" class="btn btn-primary w-100" onclick="VerificarTudo()"
                                name="verific_btn" id="verific_btn" value="Confirmar">
                            <!-- Botão de envio -->
                            <button type="submit" class="btn btn-primary w-100" style="display: none"
                                name="submeter_btn" id="submeter_btn">Registar-se</button>
                        </form>
                        </br>
                        <form method="POST" action="" style="display: inline;">
                            <button type="submit" class="btn btn-primary w-100" name="cancelar_btn" id="cancelar_btn">Cancelar</button>
                        </form>                             
                        <div class="mb-3">
                                <label for="confirmarSenha" class="form-label">Campo Obrigatório (*)</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Link para o arquivo JavaScript "script1.js" -->
        <script src="script1.js"></script>
    <script>
        // Função para obter os parâmetros da URL
        function getURLParameter(name) {
            const urlParams = new URLSearchParams(window.location.search);
            return urlParams.get(name);
        }

        // Exibe a mensagem de erro conforme o valor do parâmetro 'erro'
        window.onload = function() {
            const erro = getURLParameter('erro');
            if (erro === '1') {
                document.getElementById('erroX').innerText = 'Algo correu mal ao criar a sua conta!';
            } else if (erro === '2') {
                document.getElementById('erroX').innerText = 'Esse email já pertence a um utilizador!';
            }
        };
    </script>
  
    <?php
        // Verifica se o botão "Cancelar" foi pressionado
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancelar_btn'])) {
            session_start();
            session_abort();
            // Encerra a sessão
            session_destroy();

            
            // Redireciona para a página de Verificação do Código
            header("Location: CodeVerifyPAGE.html");
            exit();
        }
    ?>
</body>

</html>