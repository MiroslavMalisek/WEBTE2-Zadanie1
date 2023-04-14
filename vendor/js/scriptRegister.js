function checkRegisterForm(){
    var name = document.getElementById("registerFirstName").value;
    var surname = document.getElementById("registerSurname").value;
    var email = document.getElementById("registerEmail").value;
    var login = document.getElementById("registerLogin").value;
    var password = document.getElementById("registerPassword").value;
    var passwordAgain = document.getElementById("registerPasswordAgain").value;
    /*console.log(name);
    console.log(surname);
    console.log(email);
    console.log(login);
    console.log(password);
    console.log(passwordAgain);*/

    if (validateName(name)){
        if (validateSurname(surname)){
            if (validateEmail(email)){
                if (validateLogin(login)){
                    if (validatePassword(password)){
                        if (validatePasswordAgain(password, passwordAgain)){
                            return true;
                        }
                    }
                }
            }
        }
    }
    return false;

}

function validateName(name){
    var regex = /^[A-Za-z]{1,50}$/;
    if (regex.test(name)){
        document.getElementById('errorMessageName').style.display = "none";
        return true;
    }else {
        document.getElementById('errorMessageName').style.display = "block";
        return false;
    }
}

function validateSurname(surname){
    var regex = /^[A-Za-z]{1,50}$/;
    if (regex.test(surname)){
        document.getElementById('errorMessageSurname').style.display = "none";
        return true;
    }else {
        document.getElementById('errorMessageSurname').style.display = "block";
        return false;
    }
}

function validateEmail(email){
    var regex = /^(\w+([\. _]?\w+)*){3,20}@((\w+([\. _]?\w+)*)\.)+(\w{2,4})$/;
    if (regex.test(email)){
        document.getElementById('errorMessageEmail').style.display = "none";
        return true;
    }else {
        document.getElementById('errorMessageEmail').style.display = "block";
        return false;
    }
}

function validateLogin(login){
    var regex = /^\w{3,20}$/;
    if (regex.test(login)){
        document.getElementById('errorMessageLogin').style.display = "none";
        return true;
    }else {
        document.getElementById('errorMessageLogin').style.display = "block";
        return false;
    }
}

function validatePassword(password){
    var regex = /^[!-~]{8,64}$/;
    if (regex.test(password)){
        document.getElementById('errorMessagePassword').style.display = "none";
        return true;
    }else {
        document.getElementById('errorMessagePassword').style.display = "block";
        return false;
    }
}

function validatePasswordAgain(password, passwordAgain){
    var regex = /^[!-~]{8,64}$/;
    if (regex.test(passwordAgain) && (password === passwordAgain)){
        document.getElementById('errorMessagePasswordAgain').style.display = "none";
        return true;
    }else {
        document.getElementById('errorMessagePasswordAgain').style.display = "block";
        return false;
    }
}

