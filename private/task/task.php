<?php require_once DIR_FUNCTIONS . 'db_functions.php';

define('ON_PAGE', 3);
define('NAME_REGEX', '/^[A-Za-z0-9А-Яа-я]{3,256}$/');
define('EMAIL_REGEX', '/[-0-9a-zA-Z.+_]+@[-0-9a-zA-Z.+_]+.[a-zA-Z]{2,4}/'); //simple regex because it is not production
define('TEXT_REGEX', '/^[A-Za-z0-9А-Яа-я_ ]{3,256}$/');

function get_tasks($connection, $page, $sort)
{
    $limit = ON_PAGE;
    $offset = $limit * ($page - 1);
    $order = '';
    switch ($sort) {
        case 'user_name_desc' :
            $order = 'name desc';
            break;
        case 'email' :
            $order = 'email';
            break;
        case 'email_desc' :
            $order = 'email desc';
            break;
        case 'task' :
            $order = 'text';
            break;
        case 'task_desc':
            $order = 'text desc';
            break;
        case 'completed' :
            $order = 'completed';
            break;
        case 'completed_desc':
            $order = 'completed desc';
            break;
        default :
            $order = 'name';
            break;
    }
    $sql = "select * from tasks order by $order limit $limit offset $offset";
    $result = $connection->query($sql);
    if ($result->num_rows === 0) {
        exit('Nothing found');
    }
    return $result;
}

function get_last_page($connection)
{
    $sql = 'select count(*) as count from tasks';
    $result = $connection->query($sql);
    $count = 0;
    while ($row = mysqli_fetch_array($result)) {
        $count = $row['count'];
    }
    return ceil($count / ON_PAGE);
}

function convert_task_result($result)
{
    $tasks = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $task = [];
        $task['id'] = $row['id'];
        $task['user_name'] = $row['name'];
        $task['email'] = $row['email'];
        $task['text'] = $row['text'];
        $task['completed'] = $row['completed'];
        $task['redacted'] = $row['redacted'];
        $tasks[] = $task;
    }
    return $tasks;
}

function save_task($connection, $task)
{
    $sql = 'insert into tasks (name, email, text) values (?, ?, ?)';
    $statement = $connection->prepare($sql);
    $statement->bind_param("sss", $task['name'], $task['email'], $task['text']);
    $statement->execute();
    if ($statement->error) {
        $statement->close();
        return $statement->error;
    }
    $statement->close();
    return 'task added!';
}

function edit_task($connection, $task)
{
    session_start();
    if (!isset($_SESSION['user'])) {
        header('Location: http://bee-jee.zzz.com.ua/');
        exit();
    }
    $changed = false;
    if ($task['text'] != $task['old_text']) {
        $changed = true;
    }
    $completed = 0;
    if ($task['completed']) {
        $completed = 1;
    }
    if (preg_match(TEXT_REGEX, $task['text']) == false) {
        return 'Check text!';
    } else {
        $sql = "update tasks set text=?, completed=?, redacted=true where id=?";
        if (!$changed) {
            $sql = "update tasks set text=?, completed=? where id=?";
        }
        $statement = $connection->prepare($sql);
        $statement->bind_param("sdd", $task['text'], $completed, $task['id']);
        $statement->execute();
        if ($statement->error) {
            $statement->close();
            return $statement->error;
        }
        $statement->close();
    }
    return '';
}

function validate_task($task)
{
    $errors = [];
    if (!$task['name']) {
        $errors['name_error'] = 'Name must be not empty';
    } else {
        if (preg_match(NAME_REGEX, trim($task['name'])) == false) {
            $errors['name_error'] = 'Not valid name';
        }
    }
    if (!$task['email']) {
        $errors['email_error'] = 'Email must be not empty';
    } else {
        if (preg_match(EMAIL_REGEX, $task['email']) == false) {
            $errors['email_error'] = 'Not valid email';
        }
    }
    if (!$task['text']) {
        $errors['task_error'] = 'Text must be not empty';
    } else {
        if (preg_match(TEXT_REGEX, $task['text']) == false) {
            $errors['task_error'] = 'Not valid text';
        }
    }
    return $errors;
}
