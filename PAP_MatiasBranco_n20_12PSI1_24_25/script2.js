function VerificarCampos() {
    const email = document.getElementById("email").value;
    const erro1 = document.getElementById("erro1");
    let isValid = true;

    if (!email || !email.includes("@")) {
        erro1.textContent = "Insira um email válido.";
        isValid = false;
    } else {
        erro1.textContent = "";
    }

    if (isValid) {
        document.getElementById("verific_btn").style.display = "none";
        document.getElementById("submeter_btn").style.display = "block";
        
    }
}
