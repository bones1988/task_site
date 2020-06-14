<?php require_once 'private/config.php';
require_once DIR_FUNCTIONS . 'db_functions.php';
require_once DIR_TASK . 'task.php';
require_once DIR_USER . 'user.php';
session_start();
$connection = db_connect();
if (isset($_POST['id'])) {
    $task_edit = [];
    $task_edit['id'] = $_POST['id'];
    $task_edit['text'] = htmlspecialchars($_POST['text']);
    $task_edit['completed'] = $_POST['completed'];
    $task_edit['old_text'] = htmlspecialchars($_POST['old_text']);
    $edit_error = edit_task($connection, $task_edit);
}

$page = 1;
$max_page = get_last_page($connection);
if (isset($_GET) && ($_GET['logout'])) {
    logout();
}
if (isset($_GET) && isset($_GET['page'])) {
    $page = intval($_GET['page']);
}
if ($page > $max_page) {
    $page = $max_page;
}
if ($page < 1) {
    $page = 1;
}
$sort = 'user_name';
if (isset($_GET) && isset($_GET['sort'])) {
    $sort = $_GET['sort'];
}
$tasks = get_tasks($connection, $page, $sort);
$tasks = convert_task_result($tasks);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Task manager</title>
    <link rel="stylesheet" href="http://bee-jee.zzz.com.ua/index.css">
</head>
<body>
<? if (isset($_SESSION) && isset($_SESSION['user'])): ?>
    <a class="login_button" href="?logout=true">выйти</a>
<? endif; ?>
<? if (!isset($_SESSION) || !isset($_SESSION['user'])): ?>
    <a class="login_button" href="login.php">войти</a>
<? endif; ?>
<? if ($edit_error): ?>
    <p><?= $edit_error; ?></p>
<? endif; ?>

<table class="task_table">
    <tr>
        <th>Имя пользователя<a href="?page=<?= $page; ?>&sort=user_name">&#9650</a> <a
                    href="?page=<?= $page; ?>&sort=user_name_desc">&#9660</a></th>
        <th>E-mail<a href="?page=<?= $page; ?>&sort=email">&#9650</a> <a href="?page=<?= $page; ?>&sort=email_desc">&#9660</a>
        </th>
        <th>Текст задачи<a href="?page=<?= $page; ?>&sort=task">&#9650</a> <a href="?page=<?= $page; ?>&sort=task_desc">&#9660</a>
        </th>
        <th>Статус<a href="?page=<?= $page; ?>&sort=completed">&#9650</a> <a
                    href="?page=<?= $page; ?>&sort=completed_desc">&#9660</a></th>
        <? if (isset($_SESSION) && isset($_SESSION['user'])): ?>
            <th></th>
        <? endif; ?>
        <th>отредактированно<br>администратором</th>
    </tr>
    <? foreach ($tasks as $task): ?>
        <tr>
            <? if (isset($_SESSION) && isset($_SESSION['user'])): ?>
            <form action="" method="post">
                <? endif; ?>
                <td><?= $task['user_name']; ?></td>
                <td><?= $task['email']; ?></td>
                <? if (!isset($_SESSION) || !isset($_SESSION['user'])): ?>
                    <td><?= $task['text']; ?></td>
                    <td><? if ($task['completed']) {
                            echo '&#10003';
                        } ?></td>
                <? endif; ?>
                <? if (isset($_SESSION) && isset($_SESSION['user'])): ?>
                    <td><input class="text_input" type="text" name="text" value="<?= $task['text']; ?>"></td>
                <? endif; ?>
                <? if (isset($_SESSION) && isset($_SESSION['user'])): ?>
                <?
                $checked = false;
                if ($task['completed']) {
                    $checked = true;
                } ?>
                <td><input type="checkbox" <? if ($checked) {
                        echo 'checked';
                    } ?> name="completed"></td>
                <td>
                    <input type="hidden" name="id" value="<?= $task['id']; ?>">
                    <input type="hidden" name="old_text" value="<?= $task['text']; ?>">
                    <input type="submit" value="Изменить">
                </td>
            </form>
        <? endif; ?>
            <td><? if ($task['redacted']) {
                    echo '&#10003';
                } ?></td>
        </tr>
    <? endforeach; ?>
    <tr>
        <td colspan="5">
            <a href="?page=1&sort=<?= $sort; ?>">first</a>
            <? if ($page > 1): ?>
                <a href="?page=<?= $page - 1; ?>&sort=<?= $sort; ?>">prev</a>
            <? endif; ?>
            <?= $page ?>
            <? if ($page < $max_page): ?>
                <a href="?page=<?= $page + 1; ?>&sort=<?= $sort; ?>">next</a>
            <? endif; ?>
            <a href="?page=<?= $max_page; ?>&sort=<?= $sort; ?>">last</a>
        </td>
    </tr>
</table>
<? if (isset($_POST['add'])) {
    $task_add = [];
    $task_add['name'] = htmlspecialchars($_POST['name']);
    $task_add['email'] = htmlspecialchars($_POST['email']);
    $task_add['text'] = htmlspecialchars($_POST['task']);
    $input_errors = validate_task($task_add);
    if (empty($input_errors)) {
        $save = save_task($connection, $task_add);
    }
}; ?>
<form class="add_form" action="" method="post">
    <fieldset>
        <legend>
            Добавление задачи
        </legend>
        <p>
            <input type="text" name="name"/>
            ваше имя
            <?= $input_errors['name_error']; ?>
        </p>
        <p>
            <input type="text" name="email"/>
            ваш email <?= $input_errors['email_error']; ?>
        </p>
        <p>
            <input type="text" name="task"/>
            задача <?= $input_errors['task_error']; ?>
        </p>
        <input type="hidden" name="add" value="1">
        <p><input type="submit" value="Добавить задачу"/></p>
        <? if ($save): ?>
            <p><?= $save; ?></p>
        <? endif; ?>
    </fieldset>
</form>

<? db_disconnect($connection); ?>
</body>
</html>
