<?php

$config = file_get_contents('/etc/issabel.conf');


if (preg_match('/mysqlrootpwd=(.*)/', $config, $matches)) {
    $rootpw = trim($matches[1]);
} else {
    die('رمز MySQL در فایل کانفیگ یافت نشد!');
}


$mysqli = new mysqli("localhost", "root", $rootpw, "asterisk");

if ($mysqli->connect_error) {
    die("خطا در اتصال به پایگاه داده: " . $mysqli->connect_error);
}


?>
