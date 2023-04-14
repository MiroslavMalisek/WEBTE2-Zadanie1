function check2fa(){
    var code2fa = document.getElementById("2fa").value;
    var regex = /^\d{6}$/;
    console.log(code2fa);
    if (regex.test(code2fa)){
        document.getElementById('errorMessage').style.display = "none";
        console.log("true");
        return true;
    }else {
        document.getElementById('errorMessage').style.display = "block";
        console.log("false");
        return false;
    }

}