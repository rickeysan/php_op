<?php

require('function.php');
debug('$_POSTの中身：'.print_r($_POST,true));

if(empty($_POST)){
    debug('入った');
    $_SESSION['sample'] = 'abc';
}

debug('$_SESSIONの中身：'.print_r($_SESSION,true));





?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <h1>サンプル</h1>
    <form action="" method="post">
        <input type="submit" value="送信する">
    </form>
</body>
</html>