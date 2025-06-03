<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


if (!defined('DB_HOST')) {
    define('DB_HOST', 'localhost');
}
if (!defined('DB_USER')) {
    define('DB_USER', 'root');
}
if (!defined('DB_PASS')) {
    define('DB_PASS', '');
}
if (!defined('DB_NAME')) {
    define('DB_NAME', 'car_rental');
}


try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    if ($conn->connect_error) {
        throw new Exception("Ошибка подключения: " . $conn->connect_error);
    }


    $conn->set_charset("utf8mb4");
} catch (Exception $e) {
    die("Ошибка: " . $e->getMessage());
}


$lang = $_SESSION['lang'] ?? 'ru';

if (isset($_GET['lang']) && in_array($_GET['lang'], ['ru', 'en', 'et'])) {
    if ($_GET['lang'] !== $lang) {
        $_SESSION['lang'] = $_GET['lang'];


        $params = $_GET;
        unset($params['lang']);


        $redirect_url = strtok($_SERVER['REQUEST_URI'], '?');
        


        if (!empty($params)) {
            $redirect_url .= '?' . http_build_query($params);
        }
        
        header("Location: $redirect_url");
        exit;
    }
}


include 'translations.php';
?>