<?php
ob_start();
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

if (file_exists('./../../sidebar.php')) {
    include './../../sidebar.php';
} else {
    die("فایل sidebar.php پیدا نشد.");
}

// اتصال به دیتابیس
if (file_exists('./../../db.php')) {
    include './../../db.php';
} else {
    die("بانک اطلاعاتی پیدا نشد");
}



$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $survey_name       = isset($_POST['survey_name']) ? $_POST['survey_name'] : '';
    $status            = isset($_POST['status']) ? $_POST['status'] : 'inactive';
    $max_invalid       = isset($_POST['max_invalid']) ? $_POST['max_invalid'] : '3';
    $queue_select      = isset($_POST['queue_select']) ? $_POST['queue_select'] : '';
    $min_score_record  = isset($_POST['min_score_record']) ? $_POST['min_score_record'] : 'disabled';
    $max_score_record  = isset($_POST['max_score_record']) ? $_POST['max_score_record'] : '5';
    $operator_identity = isset($_POST['operator_identity']) ? $_POST['operator_identity'] : 'disabled';

    // آپلود فایل صوتی
    $uploaded_path = '';
    if (isset($_FILES['survey_audio']) && $_FILES['survey_audio']['error'] == 0) {
        $filename = 'survey_' . time() . '.wav';
        $target_path = '/var/lib/asterisk/sounds/custom/' . $filename;
        if (move_uploaded_file($_FILES['survey_audio']['tmp_name'], $target_path)) {
            $uploaded_path = $filename;
        } else {
            $message = 'خطا در آپلود فایل صوتی.';
            $message_type = 'error';
        }
    }

    // بررسی تکراری نبودن صف
    if (!$message) {
        $stmt_check_queue = $mysqli->prepare("SELECT id FROM survey_property WHERE queue = ?");
        $stmt_check_queue->bind_param("s", $queue_select);
        $stmt_check_queue->execute();
        $stmt_check_queue->store_result();

        if ($stmt_check_queue->num_rows > 0) {
            $message = "⚠️ برای این صف قبلاً تنظیمات نظرسنجی ثبت شده است. لطفاً ابتدا آن را ویرایش یا حذف کنید.";
            $message_type = 'warning';
        }
        $stmt_check_queue->close();
    }

    if (!$message) {
        // تنظیم مقصد در صف در صورت فعال بودن نظرسنجی
        if ($status == 'active' && !empty($queue_select)) {
            $description = "Survey";
            $destination = "4455";

            $stmt_check = $mysqli->prepare("SELECT id FROM miscdests WHERE description = ? AND destdial = ?");
            $stmt_check->bind_param("ss", $description, $destination);
            $stmt_check->execute();
            $stmt_check->store_result();

            if ($stmt_check->num_rows > 0) {
                $stmt_check->bind_result($misc_id);
                $stmt_check->fetch();
            } else {
                $stmt_misc = $mysqli->prepare("INSERT INTO miscdests (description, destdial) VALUES (?, ?)");
                $stmt_misc->bind_param("ss", $description, $destination);
                if ($stmt_misc->execute()) {
                    $misc_id = $stmt_misc->insert_id;
                } else {
                    die("خطا در ایجاد Misc Destination.");
                }
                $stmt_misc->close();
            }
            $stmt_check->close();

            $goto_value = "ext-miscdests," . $misc_id . ",1";
            $stmt_queue = $mysqli->prepare("UPDATE queues_config SET destcontinue = ? WHERE extension = ?");
            $stmt_queue->bind_param("ss", $goto_value, $queue_select);
            $stmt_queue->execute();
            $stmt_queue->close();
        }

        // درج اطلاعات فرم در جدول
        $stmt = $mysqli->prepare("INSERT INTO survey_property (survey_name, audio_file, status, max_invalid, queue, min_score_record, max_score_record, operator_identity) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        if ($stmt === false) {
            die("خطا در آماده‌سازی دستور SQL: " . $mysqli->error);
        }

        $stmt->bind_param("ssssssss", $survey_name, $uploaded_path, $status, $max_invalid, $queue_select, $min_score_record, $max_score_record, $operator_identity);

       if ($stmt->execute()) {
    // اجرای دستور ریلود ایزابل با sudo
    $output = shell_exec('sudo /usr/sbin/amportal a reload 2>&1');

    // فیلتر کردن خروجی برای حذف پیام‌های اضافی ترمینال
    $lines = explode("\n", $output);
    $filtered = array_filter($lines, function($line) {
        return stripos($line, 'No entry for terminal') === false &&
               stripos($line, 'using dumb terminal settings.') === false;
    });
    $output_clean = implode("\n", $filtered);

    // پیام موفقیت
    $message = "✅ اطلاعات با موفقیت ذخیره شد و ایزابل ریلود شد.";
    $message_type = "success";

    // نمایش خروجی تمیز شده
    echo "<pre style='background:#111;color:#0f0;padding:15px;border-radius:8px;margin-top:20px;'>";
    echo "=== خروجی amportal a reload ===\n";
    echo htmlspecialchars($output_clean);
    echo "\n===============================";
    echo "</pre>";

    // ریدایرکت بعد از 3 ثانیه
    echo "<script>
            setTimeout(function(){
                window.location.href = 'settings.php';
            }, 3000);
          </script>";
    exit;
} else {
    $message = "❌ خطا در ذخیره‌سازی اطلاعات: " . $stmt->error;
    $message_type = "error";
}



        $stmt->close();
    }
}
?>

<!-- فونت وزیر -->
<link href="https://cdn.fontcdn.ir/Font/Persian/Vazir/Vazir.css" rel="stylesheet" />


<div class="min-h-screen p-6 flex items-center justify-center">
  <form method="POST" enctype="multipart/form-data" class="bg-gradient-to-r from-gray-100 to-gray-350 w-full max-w-4xl bg-white rounded-2xl shadow-lg  p-8 space-y-6">

    <?php if ($message): ?>
      <?php 
      $colors = [
          'success' => 'green',
          'error' => 'red',
          'warning' => 'yellow'
      ];
      $icons = [
          'success' => '<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 inline mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>',
          'error' => '<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 inline mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>',
          'warning' => '<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 inline mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M12 9v2m0 4v.01M12 12h.01"/></svg>',
      ];
      $color = isset($colors[$message_type]) ? $colors[$message_type] : 'gray';
      $icon = isset($icons[$message_type]) ? $icons[$message_type] : '';
      ?>
      <div class="max-w-4xl mx-auto my-6 px-6 py-4 rounded-lg bg-<?= $color ?>-100 border border-<?= $color ?>-300 text-<?= $color ?>-800 shadow-md flex items-center">
          <?= $icon ?>
          <span class="text-lg"><?= $message ?></span>
      </div>
    <?php endif; ?>

    <h2 class="text-xl font-semibold text-gray-800 mb-6 border-b-2 border-gray-300 pb-2 flex items-center gap-3">
      <i class="fas fa-cogs text-3xl"></i> ایجاد تنظیمات نظرسنجی
    </h2>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <div>
        <label class="block text-gray-600 mb-1">نام نظرسنجی</label>
        <input type="text" name="survey_name" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-gray-400" required>
      </div>

      <div>
        <label for="survey_audio" class="block mb-2 font-semibold text-gray-700 select-none">فایل صوتی نظرسنجی (WAV)</label>
        <label class="cursor-pointer inline-block w-full border border-gray-300 rounded-md px-4 py-2 text-gray-700 bg-white hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-400 transition flex items-center justify-center">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M12 12v8m0 0l-4-4m4 4l4-4m-4-8v4" />
          </svg>
          <span>فایل صوتی خود را انتخاب کنید</span>
          <input type="file" id="survey_audio" name="survey_audio" accept=".wav,audio/wav" class="hidden" />
        </label>
      </div>

      <div>
        <label class="block text-gray-600 mb-1">وضعیت نظرسنجی</label>
        <select name="status" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-gray-400">
          <option value="inactive">غیرفعال</option>
          <option value="active">فعال</option>
        </select>
      </div>

      <div>
        <label class="block text-gray-600 mb-1">صف مربوطه</label>
        <select name="queue_select" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-gray-400" required>
          <?php
          $result = $mysqli->query("SELECT extension, descr FROM queues_config");
          while ($row = $result->fetch_assoc()) {
              echo "<option value='{$row['extension']}'>{$row['extension']} - {$row['descr']}</option>";
          }
          ?>
        </select>
      </div>

      <div>
        <label class="block text-gray-600 mb-1">حداکثر تعداد انتخاب اشتباه</label>
        <select name="max_invalid" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-gray-400">
          <option value="1">1</option>
          <option value="2">2</option>
          <option value="3" selected>3</option>
        </select>
      </div>

      <div>
        <label class="block text-gray-600 mb-1">حداقل امتیاز برای ضبط شکایت مشتری</label>
        <select name="min_score_record" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-gray-400">
          <option value="disabled">غیرفعال</option>
          <option value="1">1</option>
          <option value="2">2</option>
        </select>
      </div>

      <div>
        <label class="block text-gray-600 mb-1">سقف امتیاز برای ضبط پیام مشتری</label>
        <select name="max_score_record" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-gray-400">
          <option value="0">غیرفعال</option>
          <option value="3">3</option>
          <option value="4">4</option>
          <option value="5" selected>5</option>
        </select>
      </div>


      <div>
        <label class="block text-gray-600 mb-1">بیان شماره اپراتور</label>
        <select name="operator_identity" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-gray-400">
          <option value="disabled">غیرفعال</option>
          <option value="name">نام اپراتور</option>
          <option value="number">شماره اپراتور</option>
        </select>
      </div>
    </div>

  <div class="pt-6 text-left">
        <button type="submit"
          class="bg-gradient-to-r from-gray-600 to-gray-800 hover:from-gray-700 hover:to-gray-900 text-white px-6 py-2 rounded-lg shadow-md transition">
          ثبت اطلاعات
        </button>
      </div>


  </form>
</div>

<?php
ob_end_flush();
if (file_exists('./../../footer.php')) {
    include './../../footer.php';
}
?>
