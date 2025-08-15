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

// گرفتن id از URL
if (!isset($_GET['id'])) {
    die("شناسه تنظیمات برای ویرایش مشخص نشده است.");
}
$id = intval($_GET['id']);

// بارگذاری اولیه داده‌ها برای نمایش در فرم
$stmt = $mysqli->prepare("SELECT id, survey_name, audio_file, status, max_invalid, queue, min_score_record, max_score_record, operator_identity FROM survey_property WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->bind_result($f_id, $survey_name, $audio_file, $status, $max_invalid, $queue, $min_score_record, $max_score_record, $operator_identity);

if (!$stmt->fetch()) {
    die("تنظیمی با این شناسه یافت نشد.");
}
$stmt->close();

// اگر فرم ارسال شد، داده‌ها را بروزرسانی کن
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // دریافت داده‌های ارسال شده از فرم
    $survey_name_post       = isset($_POST['survey_name']) ? $_POST['survey_name'] : '';
    $status_post            = isset($_POST['status']) ? $_POST['status'] : 'inactive';
    $max_invalid_post       = isset($_POST['max_invalid']) ? $_POST['max_invalid'] : '3';
    $queue_select_post      = isset($_POST['queue_select']) ? $_POST['queue_select'] : '';
    $min_score_record_post  = isset($_POST['min_score_record']) ? $_POST['min_score_record'] : 'disabled';
    $max_score_record_post  = isset($_POST['max_score_record']) ? $_POST['max_score_record'] : '5';
    $operator_identity_post = isset($_POST['operator_identity']) ? $_POST['operator_identity'] : 'disabled';

    // آپلود فایل صوتی
    $uploaded_path = $audio_file;
    if (isset($_FILES['survey_audio']) && $_FILES['survey_audio']['error'] == 0) {
        $filename = 'survey_' . time() . '.wav';
        $target_path = '/var/lib/asterisk/sounds/custom/' . $filename;
        if (move_uploaded_file($_FILES['survey_audio']['tmp_name'], $target_path)) {
            $uploaded_path = $filename;
        }
    }

    // بروزرسانی misc destination
    if ($status_post == 'active' && !empty($queue_select_post)) {
        $description = "Survey";
        $destination = "4455";

        $stmt_misc = $mysqli->prepare("SELECT id FROM miscdests WHERE description = ? AND destdial = ?");
        $stmt_misc->bind_param("ss", $description, $destination);
        $stmt_misc->execute();
        $stmt_misc->store_result();

        if ($stmt_misc->num_rows > 0) {
            $stmt_misc->bind_result($misc_id);
            $stmt_misc->fetch();
        } else {
            $stmt_insert = $mysqli->prepare("INSERT INTO miscdests (description, destdial) VALUES (?, ?)");
            $stmt_insert->bind_param("ss", $description, $destination);
            $stmt_insert->execute();
            $misc_id = $stmt_insert->insert_id;
            $stmt_insert->close();
        }
        $stmt_misc->close();

        $goto_value = "ext-miscdests," . $misc_id . ",1";
        $stmt_update_queue = $mysqli->prepare("UPDATE queues_config SET destcontinue = ? WHERE extension = ?");
        $stmt_update_queue->bind_param("ss", $goto_value, $queue_select_post);
        $stmt_update_queue->execute();
        $stmt_update_queue->close();

    } else {
        $stmt_update_queue = $mysqli->prepare("UPDATE queues_config SET destcontinue = '' WHERE extension = ?");
        $stmt_update_queue->bind_param("s", $queue_select_post);
        $stmt_update_queue->execute();
        $stmt_update_queue->close();
    }

    // بروزرسانی تنظیمات
    $stmt_update = $mysqli->prepare("UPDATE survey_property SET 
        survey_name = ?,
        audio_file = ?,
        status = ?,
        max_invalid = ?,
        queue = ?,
        min_score_record = ?,
        max_score_record = ?,
        operator_identity = ?
        WHERE id = ?");
    $stmt_update->bind_param("ssssssssi",
        $survey_name_post,
        $uploaded_path,
        $status_post,
        $max_invalid_post,
        $queue_select_post,
        $min_score_record_post,
        $max_score_record_post,
        $operator_identity_post,
        $id);

    if ($stmt_update->execute()) {
        $output = shell_exec('sudo /usr/sbin/amportal a reload 2>&1');
        $message = "تنظیمات با موفقیت به‌روزرسانی شد.";
        $message_type = "success";
        
        // بارگذاری دوباره داده‌ها برای فرم
        $stmt = $mysqli->prepare("SELECT id, survey_name, audio_file, status, max_invalid, queue, min_score_record, max_score_record, operator_identity FROM survey_property WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->bind_result($f_id, $survey_name, $audio_file, $status, $max_invalid, $queue, $min_score_record, $max_score_record, $operator_identity);
        $stmt->fetch();
        $stmt->close();

    } else {
        $message = "خطا در بروزرسانی تنظیمات: " . $mysqli->error;
        $message_type = "error";
    }
}

$queues_result = $mysqli->query("SELECT extension, descr FROM queues_config ORDER BY extension");
if (!$queues_result) {
    die("خطا در دریافت لیست صف‌ها: " . $mysqli->error);
}
?>

<div class="min-h-screen p-6">
  <div class="bg-gradient-to-r from-gray-100 to-gray-350 max-w-4xl mx-auto bg-white rounded-2xl shadow-lg p-8">
    <h2 class="text-xl font-semibold text-gray-800 mb-6 border-b-2 border-gray-300 pb-2">
        <i class="fas fa-cogs  text-3xl"></i>
      ویرایش تنظیمات نظرسنجی
    </h2>
    <?php if (!empty($message)): ?>
<script>
Swal.fire({
    icon: 'success',
    title: '',
    text: '<?= $message ?>',
    timer: 1000,
    timerProgressBar: true,
    showConfirmButton: false,
    willClose: () => {
        window.location.href = 'settings.php';
    }
});
</script>
<?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="space-y-6">
      <?php if (!empty($message)): ?>
<?php 
$colors = [
    'success' => 'green',
    'error' => 'red',
    'warning' => 'yellow'
];
$icons = [
    'success' => '<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 inline ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>',
    'error' => '<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 inline ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>',
    'warning' => '<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 inline ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M12 9v2m0 4v.01M12 12h.01"/></svg>',
];
$color = isset($colors[$message_type]) ? $colors[$message_type] : 'gray';
$icon = isset($icons[$message_type]) ? $icons[$message_type] : '';
?>

<?php endif; ?>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">نام نظرسنجی</label>
          <input type="text" name="survey_name" required value="<?= htmlspecialchars($survey_name); ?>"
            class="w-full rounded-2xl border border-gray-300 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-gray-500" />
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
          <label class="block text-sm font-medium text-gray-700 mb-1">وضعیت نظرسنجی</label>
          <select name="status"
            class="w-full rounded-2xl border border-gray-300 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-gray-500">
            <option value="inactive" <?= $status == 'inactive' ? 'selected' : '' ?>>غیرفعال</option>
            <option value="active" <?= $status == 'active' ? 'selected' : '' ?>>فعال</option>
          </select>
        </div>

       <div>
  <label class="block text-sm font-medium text-gray-700 mb-1">صف مربوطه</label>

  <select disabled
    class="w-full rounded-2xl border border-gray-300 px-4 py-2 bg-gray-100 text-gray-500 cursor-not-allowed">
    <?php
    if ($queues_result) {
      while ($q = $queues_result->fetch_assoc()) {
        $selected = ($queue == $q['extension']) ? 'selected' : '';
        echo "<option value='" . htmlspecialchars($q['extension']) . "' $selected>" . htmlspecialchars($q['extension'] . " - " . $q['descr']) . "</option>";
      }
    }
    ?>
  </select>
  <!-- فیلد مخفی برای ارسال مقدار -->
  <input type="hidden" name="queue_select" value="<?= htmlspecialchars($queue) ?>">
</div>


        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">حداکثر تعداد انتخاب اشتباه</label>
          <select name="max_invalid"
            class="w-full rounded-2xl border border-gray-300 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-gray-500">
            <option value="1" <?= $max_invalid == '1' ? 'selected' : '' ?>>1</option>
            <option value="2" <?= $max_invalid == '2' ? 'selected' : '' ?>>2</option>
            <option value="3" <?= $max_invalid == '3' ? 'selected' : '' ?>>3</option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">حداقل امتیاز برای ضبط شکایت مشتری</label>
          <select name="min_score_record"
            class="w-full rounded-2xl border border-gray-300 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-gray-500">
            <option value="disabled" <?= $min_score_record == 'disabled' ? 'selected' : '' ?>>غیرفعال</option>
            <option value="1" <?= $min_score_record == '1' ? 'selected' : '' ?>>1</option>
            <option value="2" <?= $min_score_record == '2' ? 'selected' : '' ?>>2</option>
          </select>
        </div>

       <div>
          <label class="block text-gray-600 mb-1">سقف امتیاز برای ضبط پیام مشتری</label>
          <select name="max_score_record" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-gray-400">
            <option value="0" <?= $max_score_record == '0' ? 'selected' : '' ?>>غیرفعال</option>
            <option value="3" <?= $max_score_record == '3' ? 'selected' : '' ?>>3</option>
            <option value="4" <?= $max_score_record == '4' ? 'selected' : '' ?>>4</option>
            <option value="5" <?= $max_score_record == '5' ? 'selected' : '' ?>>5</option>
          </select>
        </div>



        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">بیان شماره اپراتور</label>
          <select name="operator_identity"
            class="w-full rounded-2xl border border-gray-300 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-gray-500">
            <option value="disabled" <?= $operator_identity == 'disabled' ? 'selected' : '' ?>>غیرفعال</option>
            <option value="name" <?= $operator_identity == 'name' ? 'selected' : '' ?>>نام اپراتور</option>
            <option value="number" <?= $operator_identity == 'number' ? 'selected' : '' ?>>شماره اپراتور</option>
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
</div>

<?php
if (file_exists('./../../footer.php')) {
    include './../../footer.php';
}
?>
