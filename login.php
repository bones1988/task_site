<?php
require_once 'private/config.php';
require_once DIR_USER . 'user.php';
session_start();
if (isset($_SESSION) && isset($_SESSION['user'])) {
    header('Location: http://bee-jee.zzz.com.ua/');
    exit();
}

if (!empty($_POST)) {
    $credentials = [];
    $credentials['user'] = $_POST['name'];
    $credentials['password'] = $_POST['password'];
    $error = authorize($credentials);
}
?>

<form action="" method="post">
    <p>Username: <input type="text" name="name"/></p>
    <p>Password: <input type="password" name="password"/></p>
    <? if ($error): ?>
        <p><?= $error; ?> </p>
    <? endif; ?>
    <p><input type="submit" value="Войти"/></p>
</form>
