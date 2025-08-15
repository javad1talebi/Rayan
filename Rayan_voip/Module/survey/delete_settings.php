<?php
session_start();

if (!isset($_SESSION['user']) || !isset($_SESSION['user']['role'])) {
    header("Location: /Rayan_voip/login.php");
    exit;
}

if ($_SESSION['user']['role'] !== 'admin') {
    header("Location: /Rayan_voip/index.php");
    exit;
}

if (file_exists('./../../header.php')) {
    include './../../header.php';
} else {
    die("فایل header.php پیدا نشد.");
}
// اتصال به دیتابیس
if (file_exists('./../../db.php')) {
    include './../../db.php';
} else {
    die("بانک اطلاعاتی پیدا نشد");
}


// تعریف متغیر پیام برای SweetAlert
$swalMessage = '';

if (isset($_GET['id']) && isset($_GET['queue'])) {
    $id = intval($_GET['id']);
    $queue_name = $mysqli->real_escape_string($_GET['queue']);

    // حذف از جدول تنظیمات
    $delete = $mysqli->query("DELETE FROM survey_property WHERE id = $id");

    if ($delete) {
        // ریست کردن مقدار destcontinue از جدول queues_config
        $reset = $mysqli->query("UPDATE queues_config SET destcontinue = '' WHERE extension = '$queue_name'");
        $output = shell_exec('sudo /usr/sbin/amportal a reload 2>&1');

        if ($reset) {
            $swalMessage = "success|رکورد با موفقیت حذف شد|settings.php";
        } else {
            $swalMessage = "error|خطا در ریست کردن destcontinue: ".$mysqli->error;
        }
    } else {
        $swalMessage = "error|خطا در حذف رکورد: ".$mysqli->error;
    }
} else {
    $swalMessage = "warning|اطلاعات نامعتبر.";
}

?>



<script>
document.addEventListener('DOMContentLoaded', function() {
    let msg = "<?php echo $swalMessage; ?>";
    if(msg) {
        let parts = msg.split("|");
        let icon = parts[0];
        let text = parts[1];
        let redirect = parts[2] || null;

        Swal.fire({
            icon: icon,
            timer: 1000,
            timerProgressBar: true,
            showConfirmButton: false,
            title: icon === 'success' ? 'اطلاعات با موفقیت حذف شد!' : icon === 'error' ? 'خطا' : 'هشدار',
            text: text,
           
        }).then(() => {
            if(redirect) window.location.href = redirect;
        });
    }
});
</script>

<?php
ob_end_flush();
if (file_exists('./../../footer.php')) {
    include './../../footer.php';
}