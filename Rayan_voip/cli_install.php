<?php
// اسکریپت نصب ساده برای ایجاد جداول و فایل config.php

if (file_exists('install/install.lock')) {
    exit("نصب قبلا انجام شده است.");
}

$db_host = "localhost";
$db_user = "root";
$db_pass = "123";
$db_name = "asterisk";

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die("خطا در اتصال به دیتابیس: " . $conn->connect_error);
}

$sql = file_get_contents(__DIR__ . '/install/db.sql');
if ($conn->multi_query($sql)) {
    do {
        if ($result = $conn->store_result()) {
            $result->free();
        }
    } while ($conn->next_result());
} else {
    die("خطا در اجرای دستورات SQL: " . $conn->error);
}

$config_content = "<?php\n";
$config_content .= "\$db_host = '$db_host';\n";
$config_content .= "\$db_user = '$db_user';\n";
$config_content .= "\$db_pass = '$db_pass';\n";
$config_content .= "\$db_name = '$db_name';\n";

file_put_contents(__DIR__ . '/config/config.php', $config_content);

file_put_contents(__DIR__ . '/install/install.lock', date('Y-m-d H:i:s'));

echo "نصب با موفقیت انجام شد.";
?>
