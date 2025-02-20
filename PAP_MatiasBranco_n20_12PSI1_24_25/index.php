<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exame Vigio</title>
    <link rel="icon" type="image/x-icon" href="imgs//logo.png">
    <link href="Styles_responce.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
    <section class="vh-100">
        <div class="container-fluid h-custom">
            <div class="row d-flex justify-content-center align-items-center h-100">
                <div class="col-md-9 col-lg-6 col-xl-5">
                    <img src="imgs//logotipo.png" class="img-fluid" alt="Sample image">
                </div>
                <div class="col-md-8 col-lg-6 col-xl-4 offset-xl-1">
                    <form name="form" method="POST" action="login.php">

                        <div class="divider d-flex align-items-center my-4">
                            <p class="text-center fw-bold mx-3 mb-0">Log In</p>
                        </div>

                        <!-- Email input -->
                        <div class="form-outline    mb-4">
                            <label class="form-label" for="email">Endereço Email *</label>
                            <input type="email" id="email" name="email" class="form-control form-control-lg"
                                   placeholder="O seu endereço email aqui . . ." required/>
                        </div>
                        <div id="erro1" class="text-danger"></div>

                        <!-- Password input -->
                        <div class="form-outline mb-3">
                            <label class="form-label" for="pwd">Password *</label>
                            <input type="password" id="pwd" name="pwd" class="form-control form-control-lg"
                                   placeholder="*_______*" required/>
                        </div>
                        <div id="erro2" class="text-danger"></div>

                        <div class="d-flex justify-content-between align-items-center">
                            <div class="form-check mb-0">
                                <input class="form-check-input me-2" type="checkbox" value="" id="checkbox" />
                                <label class="form-check-label" for="form2Example3">Lembrar-me</label>
                            </div>
                            <a href="recruperarPWD.php" class="text-body">Esqueceu-se da password?</a>
                        </div>

                        <div class="text-center text-lg-start mt-4 pt-2">
                            <input type="submit" class="btn btn-primary btn-lg"
                                   style="padding-left: 2.5rem; padding-right: 2.5rem;" value="Entrar">
                            <p class="small fw-bold mt-2 pt-1 mb-0">Ainda não tem conta? <a href="CodeVerifyPAGE.html"
                                class="link-danger">Registre-se</a></p>
                        </div>

                    </form>
                </div>
            </div>
        </div>

        <div class="d-flex flex-column flex-md-row text-center text-md-start justify-content-between py-4 px-4 px-xl-5 bg-primary">
            <div class="text-white mb-3 mb-md-0">@Escola Secundária Avelar Brotero - 2025</div>
        </div>
    </section>

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
                document.getElementById('erro1').innerText = 'Email incorreto, ou inexistente!';
            } else if (erro === '2') {
                document.getElementById('erro2').innerText = 'Password incorreta!';
            }
            else if (erro === '3') {
                document.getElementById('erro1').innerText = 'O seu utilizador não está autalizado!';
                document.getElementById('erro2').innerText = 'O seu utilizador não está autalizado!';
            }
            else if (erro === '4') {
                document.getElementById('erro1').innerText = 'Erro ao tentar efetuar Login!';
                document.getElementById('erro2').innerText = 'Erro ao tentar efetuar Login!';
            }
            else if (erro === '5') {
                document.getElementById('erro1').innerText = 'A sua conta foi criada! Inicie sessão!';
                document.getElementById('erro2').innerText = 'A sua conta foi criada! Inicie sessão!';
            }
        };
    </script>
</body>
</html>