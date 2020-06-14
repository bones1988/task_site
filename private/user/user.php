<?php
define('ADMIN_NAME', 'admin');
define('ADMIN_PASS', '123');

function authorize($credentials)
{
    if (!$credentials['user'] || !$credentials['password']) {
        return 'Incorrect data input';
    } else {
        if ($credentials['user'] != ADMIN_NAME || $credentials['password'] != ADMIN_PASS) {
            return 'Wrong user name or password';
        } else {
            session_start();
            $_SESSION['user'] = 'admin';
            header('Location: http://bee-jee.zzz.com.ua/');
            exit();
        }
    }
}

function logout()
{
    unset($_SESSION['user']);
    header('Location: http://bee-jee.zzz.com.ua/');
    exit();
}
