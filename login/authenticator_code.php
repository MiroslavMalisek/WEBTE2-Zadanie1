<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$fa_auth_path = '../2FA_api/PHPGangsta/GoogleAuthenticator.php';
require_once($fa_auth_path);

$config_path = '../config.php';
require($config_path);
$error_msg = "";

if (!array_key_exists('fa_code',$_SESSION)){
    header('Location: ../accessDenied.php');
}

function insert_login($db_connection){
    $sql = "INSERT INTO log_in(user_id, login_type_id) VALUES (:user, :type)";
    $stmt = $db_connection->prepare($sql);
    $stmt->bindParam(":user", $_SESSION['id_user'], PDO::PARAM_INT);
    $stmt->bindParam(":type", $_SESSION['type_login'], PDO::PARAM_INT);
    if ($stmt->execute()){
        //vsetko ok, v session nechame len dolezite veci
        $_SESSION['id_login'] = $db_connection->lastInsertId();
        unset($_SESSION['password']);
        unset($_SESSION['fa_code']);
        unset($_SESSION['qr_code']);
        unset($_SESSION['type_login']);
        return true;
    }else{
        return false;
    }
}

function register(){
    $config_path = '../config.php';
    require($config_path);
    $error_msg = "";

    try {
        $db_connection = new PDO("mysql:host=$hostname;dbname=$dbname", $dbusername, $dbpassword);
        $db_connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $hashed_password = password_hash($_SESSION['password'], PASSWORD_ARGON2ID);
        $_SESSION['password'] = $hashed_password;

        $sql = "INSERT INTO user_profile(name, surname, email, login, password, 2fa_code) VALUES (:name, :surname, :email, :login, :password, :2fa_code)";
        $stmt = $db_connection->prepare($sql);
        $stmt->bindParam(":name", $_SESSION['name'], PDO::PARAM_STR);
        $stmt->bindParam(":surname", $_SESSION['surname'], PDO::PARAM_STR);
        $stmt->bindParam(":email", $_SESSION['email'], PDO::PARAM_STR);
        $stmt->bindParam(":login", $_SESSION['login'], PDO::PARAM_STR);
        $stmt->bindParam(":password", $hashed_password, PDO::PARAM_STR);
        $stmt->bindParam(":2fa_code", $_SESSION['fa_code'], PDO::PARAM_STR);
        if ($stmt->execute()){
            $_SESSION['id_user'] = $db_connection->lastInsertId();
            //uzivatel bol zaregistrovany a prihlasime ho, vlozime zaznam prihlasenia do tabulky
            if(insert_login($db_connection)){
                $_SESSION['logged_in'] = true;
                header('Location: ../private_zone/user_zone.php');
            }else{
                $error_msg.= "<p class='errMsg'>*Nepoadrilo sa prihlásiť</p>";
                return $error_msg;
            }
        }else{
            $error_msg.= "<p class='errMsg'>*Nepoadrilo sa zaregistrovať</p>";
            return $error_msg;
        }
    }catch (PDOException $e){
        $error_msg.= "<p class='errMsg'>*Nepoadrilo sa pripojiť k databáze</p>";
        return $error_msg;
    }
    return $error_msg;
}
$error_msg = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    //ak sa na stranku dostaneme cez registraciu
    if (isset($_SESSION['qr_code']) && $_SESSION['qr_code']) {
        if (preg_match('/^\d{6}$/', trim($_POST['2fa']))) {
            //validacia zadaneho 2fa
            $g2fa = new PHPGangsta_GoogleAuthenticator();
            if ($g2fa->verifyCode($_SESSION['fa_code'], $_POST['2fa'], 2)) {
                //overili sme 2FA, vlozime prihlasenie do DB
                $error_msg.= register();
                /*try {
                    $config_path = '../config.php';
                    require_once($config_path);
                    $db_connection = new PDO("mysql:host=$hostname;dbname=$dbname", $dbusername, $dbpassword);
                    $db_connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    $sql = "INSERT INTO log_in(user_id, login_type_id) VALUES (:user, :type)";
                    $stmt = $db_connection->prepare($sql);
                    $stmt->bindParam(":user", $_SESSION['id'], PDO::PARAM_INT);
                    $stmt->bindParam(":type", $_SESSION['type_login'], PDO::PARAM_INT);
                    if ($stmt->execute()){
                        header('Location: ../oauth/restricted.php');
                    }
                }catch (PDOException $e){
                    echo "<script>console.log('Error connecting to database');</script>";
                }*/
            }else{
                $error_msg.= "<p class='errMsg'>*Nesprávny 2FA kód</p>";
            }
        }else{
            $error_msg.= "<p class='errMsg'>*2FA kód musí obsahovať presne 6 číslic</p>";
        }
    }elseif(isset($_SESSION['id_user']) && $_SESSION['id_user']){
        //cez login
        if (preg_match('/^\d{6}$/', trim($_POST['2fa']))) {
            //validacia zadaneho 2fa
            $g2fa = new PHPGangsta_GoogleAuthenticator();
            if ($g2fa->verifyCode($_SESSION['fa_code'], $_POST['2fa'], 2)) {
                try {
                    $db_connection = new PDO("mysql:host=$hostname;dbname=$dbname", $dbusername, $dbpassword);
                    $db_connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    if (insert_login($db_connection)){
                        $_SESSION['logged_in'] = true;
                        header('Location: ../private_zone/user_zone.php');
                    }else {
                        $error_msg .= "<p class='errMsg'>*Nepoadrilo sa prihlásiť</p>";
                    }
                }catch (PDOException $e){
                    $error_msg.= "<p class='errMsg'>*Nepoadrilo sa pripojiť k databáze</p>";
                }
            }else{
                $error_msg.= "<p class='errMsg'>*Nesprávny 2FA kód</p>";
            }
        }else{
            $error_msg.= "<p class='errMsg'>*2FA kód musí obsahovať presne 6 číslic</p>";
        }
    }
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>2FA Overenie</title>
    <link rel="stylesheet" href="../vendor/css/styleRegistrationQr.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">

</head>
<body>
<div class="formDiv shadow p-2 mb-2 rounded">
    <div class="title">Zadajte 2FA kód z aplikácie pre úsepšné prihlásenie:</div>
    <form class="form" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
        <div class="code">
            <div class="input_box">
                <?php
                if (isset($_SESSION['fa_code']) && $_SESSION['fa_code']) {
                    echo '<input type="text" placeholder="2FA" name="2fa" value="" id="2fa">';
                }else{
                    echo '<p class="errorMessage" id="errorMessage">*Nastala chyba pri načítaní 2FA</p>';
                }
                ?>
                <p class="errorMessage" id="errorMessage">*2FA kód musí obsahovať presne 6 číslic</p>
                <?php
                if (!empty($error_msg)){
                    echo $error_msg;
                }
                ?>
            </div>
        </div>
        <div class="button">
            <input type="submit" value="Prihlásiť">
        </div>
    </form>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js" integrity="sha384-w76AqPfDkMBDXo30jS1Sgez6pr3x5MlQ1ZAGC+nuZB+EYdgRZgiwxhTBTkF7CXvN" crossorigin="anonymous"></script>
<script src="../vendor/js/scriptAuthenitcator.js"></script>
</body>
</html>
