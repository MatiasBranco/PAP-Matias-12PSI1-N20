function VerificarTudo() {
    // Obtendo os valores dos campos
    var nome = document.getElementById("nome").value.trim();
    var email = document.getElementById("email").value.trim();
    var senha = document.getElementById("password").value.trim();
    var senha_confirmacao = document.getElementById("confirmarPassword").value.trim();

    let t1 = false, t2 = false, t3 = false;

    // Limpa mensagens de erro anteriores
    document.getElementById("erro1").innerHTML = "";
    document.getElementById("erro2").innerHTML = "";
    document.getElementById("erro3").innerHTML = "";
    document.getElementById("erro4").innerHTML = "";
    document.getElementById("erro5").innerHTML = "";

    // Validação do nome
    if (nome === "") {
        document.getElementById("erro1").innerHTML = "Preencha o nome!";
        t1 = false;
    } else {
        t1 = true;
    }

    // Validação do email
    if (email === "") {
        document.getElementById("erro2").innerHTML = "Coloque um email!";
        t2 = false;
    } else if (!VerificarEmail(email)) {
        document.getElementById("erro2").innerHTML = 'Endereço de email inválido! Exemplo: "nome@exemplo.com"';
        t2 = false;
    } else {
        t2 = true;
    }

    // Validação da senha
    const resultadoValidacao = verificarSenhaComplexa(senha);
    if (senha === "") {
        document.getElementById("erro3").innerHTML = "Coloque uma palavra-passe na sua conta!";
        t3 = false;
    } else if (resultadoValidacao != "A palavra-passe é válida.") {
        document.getElementById("erro3").innerHTML = resultadoValidacao;
        t3 = false;
    } else if (senha !== senha_confirmacao) {
        document.getElementById("erro3").innerHTML = "As passwords não estão iguais!";
        document.getElementById("erro4").innerHTML = "As passwords não estão iguais!";
        t3 = false;
    } else {
        document.getElementById("erro3").innerHTML = ""; // Remove mensagem de erro se a senha for válida
        t3 = true;
    }

    // Verificação final
    if (t1 && t2 && t3) {
        document.getElementById('verific_btn').style.display = 'none';
        document.getElementById('submeter_btn').style.display = 'block'; // Mostra o botão de envio
        return true; // Permite o envio do formulário
    }
    return false; // Impede o envio do formulário
}

// Função para verificar email
function VerificarEmail(email) {
    var re = /\S+@\S+\.\S+/;
    return re.test(email);
}

// Verificação em tempo real da senha
document.getElementById("password").addEventListener("input", function () {
    const senha = this.value.trim();
    const erro3 = document.getElementById("erro3");
    const resultadoValidacao = verificarSenhaComplexa(senha);

    // Exibir mensagem de erro se os critérios não forem atendidos
    if (resultadoValidacao !== "A senha é válida.") {
        erro3.innerHTML = resultadoValidacao;
        document.getElementById("submeter_btn").style.display = 'none'; // Esconde o botão de envio
    } else {
        erro3.innerHTML = "";
        document.getElementById("submeter_btn").style.display = 'block'; // Exibe o botão de envio
    }
});

// Verificação em tempo real de confirmação da senha
document.getElementById("confirmarPassword").addEventListener("input", function () {
    const senha = document.getElementById("password").value.trim();
    const senha_confirmacao = this.value.trim();
    const erro4 = document.getElementById("erro4");

    if (senha !== senha_confirmacao) {
        erro4.innerHTML = "As passwords não estão iguais!";
        document.getElementById("submeter_btn").style.display = 'none'; // Esconde o botão de envio
    } else {
        erro4.innerHTML = "";
        const resultadoValidacao = verificarSenhaComplexa(senha);
        if (resultadoValidacao === "A senha é válida.") {
            document.getElementById("submeter_btn").style.display = 'block'; // Exibe o botão de envio se tudo estiver certo
        }
    }
});

function verificarSenhaComplexa(senha) {
    // Definir os critérios para uma senha complexa
    const comprimentoMinimo = 8;
    const temMaiuscula = /[A-Z]/;
    const temMinuscula = /[a-z]/;
    const temNumero = /[0-9]/;
    const temCaracterEspecial = /[!@#$%^&*(),.?":{}|<>]/;

    // Verificar cada critério
    if (senha.length < comprimentoMinimo) {
        return "A palavra-passe deve ter pelo menos " + comprimentoMinimo + " caracteres.";
    }
    else if (!temMaiuscula.test(senha)) {
        return "A palavra-passe deve conter pelo menos uma letra maiúscula.";
    }
    else if (!temMinuscula.test(senha)) {
        return "A palavra-passe deve conter pelo menos uma letra minúscula.";
    }
    else if (!temNumero.test(senha)) {
        return "A palavra-passe deve conter pelo menos um número.";
    }
    else if (!temCaracterEspecial.test(senha)) {
        return "A palavra-passe deve conter pelo menos um caractere especial.";
    }
    else{
        // Caso passe por todos os critérios
        return "A palavra-passe é válida.";
    }
}
