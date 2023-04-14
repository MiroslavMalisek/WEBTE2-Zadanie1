<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


//funkcie pre backend validaciu
function checkName($name) {
    if (!preg_match('/^[A-Za-z]{1,50}$/', trim($name))) {
        return false;
    }
    return true;
}

function checkSurname($surname) {
    if (!preg_match('/^[A-Za-z]{1,50}$/', trim($surname))) {
        return false;
    }
    return true;
}

function checkEmail($email) {
    if (!preg_match('/^(\w+([\. _]?\w+)*){3,20}@((\w+([\. _]?\w+)*)\.)+(\w{2,4})$/', trim($email))) {
        return false;
    }
    return true;
}

function checkLogin($login) {
    if (!preg_match('/^\w{3,20}$/', trim($login))) {
        return false;
    }
    return true;
}

function checkPassword($password) {
    if (!preg_match('/^[!-~]{8,64}$/', trim($password))) {
        return false;
    }
    return true;
}

function checkPasswordAgain($password, $passwordAgain) {
    if (!preg_match('/^[!-~]{8,64}$/', trim($passwordAgain))) {
        return false;
    }elseif (strcmp(trim($password), trim($passwordAgain)) != 0){
        return false;
    }
    return true;
}

function checkGmail($email) {
    // Funkcia pre kontrolu, ci zadany email je gmail.
    if (!preg_match('/^[\w.+\-]+@gmail\.com$/', trim($email))) {
        return false;
    }
    return true;
}

//funkcie pre overenie registracie
function checkEmailExists($email){
    $config_path = 'config.php';
    require($config_path);
    $exist = false;

    $param_email = trim($email);

    try {
        $db_connection = new PDO("mysql:host=$hostname;dbname=$dbname", $dbusername, $dbpassword);
        $db_connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $sql = "SELECT id FROM user_profile WHERE email = :email";
        $stmt = $db_connection->prepare($sql);
        $stmt->bindParam(":email", $param_email, PDO::PARAM_STR);
        $stmt->execute();

        if ($stmt->rowCount() == 1) {
            $exist = true;
        }
        unset($stmt);
    }catch (PDOException $e){
        echo "<script>console.log('Error connecting to database');</script>";
    }

    return $exist;

}

function checkLoginExists($login){
    $config_path = 'config.php';
    require($config_path);
    $exist = false;

    $param_login = trim($login);

    try {
        $db_connection = new PDO("mysql:host=$hostname;dbname=$dbname", $dbusername, $dbpassword);
        $db_connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $sql = "SELECT id FROM user_profile WHERE login = :login";
        $stmt = $db_connection->prepare($sql);
        $stmt->bindParam(":login", $param_login, PDO::PARAM_STR);
        $stmt->execute();

        if ($stmt->rowCount() == 1) {
            $exist = true;
        }
        unset($stmt);
    }catch (PDOException $e){
        echo "<script>console.log('Error connecting to database');</script>";
    }

    return $exist;

}

/*function redirect_registration($qr_code, $user_last_id, $user_secret){
    $_SESSION['name'] = $_POST['registerFirstName'];
    $_SESSION['surname'] = $_POST['registerSurname'];
    $_SESSION['email'] = $_POST['registerEmail'];
    $_SESSION['login'] = $_POST['registerLogin'];
    $_SESSION['id'] = $user_last_id;
    $_SESSION['type_login'] = 2;
    $_SESSION['qr_code'] = $qr_code;
    $_SESSION['fa_code'] = $user_secret;

    header('Location: registration.php');
}*/

function redirect_registration(){
    $fa_auth_path = '2FA_api/PHPGangsta/GoogleAuthenticator.php';
    require_once($fa_auth_path);
    $g2fa = new PHPGangsta_GoogleAuthenticator();
    $user_secret = $g2fa->createSecret();
    $authName = trim($_POST['registerFirstName']) . ' '. trim($_POST['registerSurname']).' - '.'Olympic Games';
    $codeURL = $g2fa->getQRCodeGoogleUrl($authName, $user_secret);

    $_SESSION['name'] = trim($_POST['registerFirstName']);
    $_SESSION['surname'] = trim($_POST['registerSurname']);
    $_SESSION['email'] = trim($_POST['registerEmail']);
    $_SESSION['login'] = trim($_POST['registerLogin']);
    $_SESSION['password'] = trim($_POST['registerPassword']);
    $_SESSION['type_login'] = 2;
    $_SESSION['qr_code'] = $codeURL;
    $_SESSION['fa_code'] = $user_secret;

    header('Location: registration.php');
}
function do_registration($name, $surname, $email, $login, $password){
    $config_path = 'config.php';
    require($config_path);
    $fa_auth_path = '2FA_api/PHPGangsta/GoogleAuthenticator.php';
    require_once($fa_auth_path);

    try {
        $db_connection = new PDO("mysql:host=$hostname;dbname=$dbname", $dbusername, $dbpassword);
        $db_connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $hashed_password = password_hash($password, PASSWORD_ARGON2ID);
        $g2fa = new PHPGangsta_GoogleAuthenticator();
        $user_secret = $g2fa->createSecret();
        $authName = $name . ' '. $surname.' - '.'Olympic Games';
        $codeURL = $g2fa->getQRCodeGoogleUrl($authName, $user_secret);

        $sql = "INSERT INTO user_profile(name, surname, email, login, password, 2fa_code) VALUES (:name, :surname, :email, :login, :password, :2fa_code)";
        $stmt = $db_connection->prepare($sql);
        $stmt->bindParam(":name", $name, PDO::PARAM_STR);
        $stmt->bindParam(":surname", $surname, PDO::PARAM_STR);
        $stmt->bindParam(":email", $email, PDO::PARAM_STR);
        $stmt->bindParam(":login", $login, PDO::PARAM_STR);
        $stmt->bindParam(":password", $hashed_password, PDO::PARAM_STR);
        $stmt->bindParam(":2fa_code", $user_secret, PDO::PARAM_STR);
        if ($stmt->execute()){
            $user_insert_id = $db_connection->lastInsertId();
            redirect_registration($codeURL, $user_insert_id, $user_secret);
        }

    }catch (PDOException $e){
        echo "<script>console.log('Error connecting to database');</script>";
    }
}


if($_SERVER["REQUEST_METHOD"] == "POST"){
    //backend validácia
    $error_msg = "";
    $qr_code = "";
    if (!checkName($_POST['registerFirstName'])){
        $error_msg.= "<p class='errMsg'>*Meno môže obsahovať len 1 - 50 písmen</p>";
    }
    if (!checkSurname($_POST['registerSurname'])){
        $error_msg.= "<p class='errMsg'>*Priezvisko môže obsahovať len 1 - 50 písmen</p>";
    }
    if (!checkEmail($_POST['registerEmail'])){
        $error_msg.= "<p class='errMsg'>*Email je v nesprávnom tvare</p>";
    }
    if (!checkLogin($_POST['registerLogin'])){
        $error_msg.= "<p class='errMsg'>*Login môže obsahovať len alfanumerické znaky alebo podtŕžnik v zosahu 3-20 znakov</p>";
    }
    if (!checkPassword($_POST['registerPassword'])){
        $error_msg.= "<p class='errMsg'>*Heslo musí byť v rozsahu 8 - 64 znakov</p>";
    }
    if (!checkPasswordAgain($_POST['registerPassword'], $_POST['registerPasswordAgain'])){
        $error_msg.= "<p class='errMsg'>*Heslá sa nezhodujú</p>";
    }

    //email a user check
    if (checkEmailExists($_POST['registerEmail'])){
        if (checkGmail($_POST['registerEmail'])){
            //ak zadany mail existuje v DB a je to gmail, treba sa prihlasit cez OAUTH
            require_once ('oauth_api/vendor/autoload.php');
            $client = new Google\Client();
            $client->setAuthConfig('OAuth.json');
            $redirect_uri = "https://site152.webte.fei.stuba.sk/zadanie1/oauth/redirect.php";
            $client->setRedirectUri($redirect_uri);

            // Definovanie Scopes - rozsah dat, ktore pozadujeme od pouzivatela z jeho Google uctu.
            $client->addScope("email");
            $client->addScope("profile");

            // Vytvorenie URL pre autentifikaciu na Google server - odkaz na Google prihlasenie.
            $auth_url = $client->createAuthUrl();
            $error_msg.='<div>
                            <p class="errMsg">*Prihláste sa daným gmail emailom cez OAuth2</p>
                            <a href="' . filter_var($auth_url, FILTER_SANITIZE_URL) . '" class="btn btn-google shadow p-2 mb-2 rounded"><i class="bi bi-google"></i>Sign in with Google</a>
                        </div>';
        }else{
            //zadany mail existuje, ale nie je to gmail
            $error_msg.= "<p class='errMsg'>*Zadaný mail už existuje</p>";
        }
    }else{
        //zadany mail este neexistuje, overit login
        if (checkLoginExists($_POST['registerLogin'])){
            $error_msg.= "<p class='errMsg'>*Zadaný login už existuje, zadajte iný</p>";
        }else{
            //aj login je jedinecny, mozeme zaregistrovat
            //do_registration(trim($_POST['registerFirstName']), trim($_POST['registerSurname']), trim($_POST['registerEmail']), trim($_POST['registerLogin']), trim($_POST['registerPassword']));
            redirect_registration();
        }
    }
}



?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Registrácia</title>
    <link rel="stylesheet" href="vendor/css/styleRegister.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
</head>
<body>
<div class="container-md form shadow p-2 mb-2 rounded">
    <div class="title">Registrácia</div>
    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" onsubmit="return checkRegisterForm()" method="post">
        <div class="user__details">
            <div class="input__box">
                <span class="details">Meno</span>
                <input type="text" placeholder="Zadajte meno" id="registerFirstName" name="registerFirstName" value="<?php echo isset($_POST['registerFirstName']) ? $_POST['registerFirstName'] : '' ?>">
                <p class="errorMessage" id="errorMessageName">*Meno je v nesprávnom tvare</p>
            </div>
            <div class="input__box">
                <span class="details">Priezvisko</span>
                <input type="text" placeholder="Zadajte priezvisko" id="registerSurname" name="registerSurname" value="<?php echo isset($_POST['registerSurname']) ? $_POST['registerSurname'] : '' ?>">
                <p class="errorMessage" id="errorMessageSurname">*Priezvisko je v nesprávnom tvare</p>
            </div>
            <div class="input__box">
                <span class="details">Email</span>
                <input type="text" placeholder="napr. johnsmith@gmail.com" id="registerEmail" name="registerEmail" value="<?php echo isset($_POST['registerEmail']) ? $_POST['registerEmail'] : '' ?>">
                <p class="errorMessage" id="errorMessageEmail">*Email je v nesprávnom tvare</p>
            </div>
            <div class="input__box">
                <span class="details">Login</span>
                <input type="text" placeholder="Zadajte login, ktorý chcete používať" id="registerLogin" name="registerLogin" value="<?php echo isset($_POST['registerLogin']) ? $_POST['registerLogin'] : '' ?>">
                <p class="errorMessage" id="errorMessageLogin">*Login je v nesprávnom tvare</p>
            </div>
            <div class="input__box">
                <span class="details">Heslo</span>
                <input type="password" placeholder="********" id="registerPassword" name="registerPassword">
                <p class="errorMessage" id="errorMessagePassword">*Heslo je v nesprávnom tvare</p>
            </div>
            <div class="input__box">
                <span class="details">Potvrďte heslo</span>
                <input type="password" placeholder="********" id="registerPasswordAgain" name="registerPasswordAgain">
                <p class="errorMessage" id="errorMessagePasswordAgain">*Heslá sa nezhodujú</p>
            </div>

        </div>
        <?php
        if (!empty($error_msg)){
            echo $error_msg;
        }
        ?>
        <div class="button">
            <input type="submit" value="Registrovať">
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js" integrity="sha384-w76AqPfDkMBDXo30jS1Sgez6pr3x5MlQ1ZAGC+nuZB+EYdgRZgiwxhTBTkF7CXvN" crossorigin="anonymous"></script>
<script src="vendor/js/scriptRegister.js"></script>
</body>
</html>
