<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

require_once ('../oauth_api/vendor/autoload.php');

$config_path = '../config.php';
require_once($config_path);

$client = new Google\Client();
$client->setAuthConfig('../OAuth.json');

$redirect_uri = "https://site152.webte.fei.stuba.sk/zadanie1/oauth/redirect.php";
$client->setRedirectUri($redirect_uri);

// Definovanie Scopes - rozsah dat, ktore pozadujeme od pouzivatela z jeho Google uctu.
$client->addScope("email");
$client->addScope("profile");

function registerOAuth($db_connection, $account_info){
    $sql = "INSERT INTO user_profile(name, surname, email) VALUES (:name, :surname, :email)";
    $stmt = $db_connection->prepare($sql);
    $stmt->bindParam(":name", $account_info->givenName, PDO::PARAM_STR);
    $stmt->bindParam(":surname", $account_info->familyName, PDO::PARAM_STR);
    $stmt->bindParam(":email", $account_info->email, PDO::PARAM_STR);
    if ($stmt->execute()){
        //uzivtel bol zaregistrovany do databazy
        //vlozit prihlasenie
        //terajsie prihlasenie je cez OAuth
        $user_insert_id = $db_connection->lastInsertId();
        loginOAuth($db_connection, $user_insert_id);
        $_SESSION['id_user'] = $user_insert_id;
        $type = 1;
        $sql = "INSERT INTO log_in(user_id, login_type_id) VALUES (:user, :type)";
        $stmt = $db_connection->prepare($sql);
        $stmt->bindParam(":user", $user_insert_id, PDO::PARAM_INT);
        $stmt->bindParam(":type", $type, PDO::PARAM_INT);
        if ($stmt->execute()){

        }
    }

}

function loginOAuth($db_connection, $user_id){
    $type = 1;
    $sql = "INSERT INTO log_in(user_id, login_type_id) VALUES (:user, :type)";
    $stmt = $db_connection->prepare($sql);
    $stmt->bindParam(":user", $user_id, PDO::PARAM_INT);
    $stmt->bindParam(":type", $type, PDO::PARAM_INT);
    if ($stmt->execute()){
        $_SESSION['id_login'] = $db_connection->lastInsertId();
    }
}

// Ak bolo prihlasenie uspesne, Google server nam posle autorizacny kod v URI,
// ktory ziskame pomocou premennej $_GET['code']. Pri neuspesnom prihlaseni tento kod nie je odoslany.
if (isset($_GET['code'])) {
    // Na zaklade autentifikacneho kodu ziskame "access token".
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
    $client->setAccessToken($token['access_token']);

    // Inicializacia triedy OAuth2, pomocou ktorej ziskame informacie pouzivatela na zaklade Scopes.
    $oauth = new Google\Service\Oauth2($client);
    $account_info = $oauth->userinfo->get();

    // Ziskanie dat pouzivatela z Google uctu. Tieto data sa nachadzaju aj v tokene po jeho desifrovani.
    $g_email = $account_info->email;
    $g_name = $account_info->givenName;
    $g_surname = $account_info->familyName;

    //praca s DB
    try {
        $db_connection = new PDO("mysql:host=$hostname;dbname=$dbname", $dbusername, $dbpassword);
        $db_connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $sql = "SELECT id FROM user_profile WHERE email = :email";
        $stmt = $db_connection->prepare($sql);
        $stmt->bindParam(":email", $g_email, PDO::PARAM_STR);
        $stmt->execute();

        if ($stmt->rowCount() == 1) {
            //uzivatel s takym gmailom uz existuje, len sa prihlasuje
            $user =$stmt->fetch(PDO::FETCH_ASSOC);
            loginOAuth($db_connection, $user['id']);
            $_SESSION['id_user'] = $user['id'];
        }else{
            //uzivatel s takym gmailom este neexistuje, treba ho aj registrovat
            registerOAuth($db_connection, $account_info);
        }
        // Ulozime potrebne data do session.
        $_SESSION['logged_in'] = true;
        $_SESSION['name'] = $account_info->givenName;
        $_SESSION['surname'] = $account_info->familyName;
        $_SESSION['email'] = $account_info->email;
        unset($stmt);
        header('Location: ../private_zone/user_zone.php');
    }catch (PDOException $e){
        echo "<script>console.log('Error connecting to database');</script>";
    }

    // Ulozime potrebne data do session.
    /*$_SESSION['access_token'] = $token['access_token'];
    $_SESSION['email'] = $g_email;
    $_SESSION['id'] = $g_id;
    $_SESSION['fullname'] = $g_fullname;
    $_SESSION['name'] = $g_name;
    $_SESSION['surname'] = $g_surname;*/
}else{

    header('Location: ../accessDenied.php');
}
//header('Location: restricted.php');
?>
