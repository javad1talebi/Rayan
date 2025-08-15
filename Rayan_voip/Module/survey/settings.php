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

$query = "SELECT * FROM survey_property ORDER BY id DESC";
$result = $mysqli->query($query);
if (!$result) {
    die("خطا در اجرای کوئری: " . $mysqli->error);
}
?>



<div class="bg-white max-w-7xl mx-auto p-6 rtl min-h-screen">
  <!-- هدر صفحه -->
  <div class="flex justify-between items-center mb-5">
  <h1 class="text-3xl text-gray-900 flex items-center space-x-3 rtl:space-x-reverse">
  <i class="fas fa-cogs text-gray-700 text-3xl"></i>
  مدیریت تنظیمات نظرسنجی
</h1>

   <a href="add_settings.php" class="bg-gradient-to-r from-gray-600 to-gray-800 hover:from-gray-700 hover:to-gray-900 text-white rounded-md px-4 py-2 shadow flex flex-row-reverse items-center space-x-2 rtl:space-x-reverse transition-all duration-200">
  
  <span>افزودن تنظیمات جدید</span>
  <i class="fas fa-cogs  text-3xl"></i>
</a>


  </div>

  <!-- کارت جدول -->
  <div class="bg-white shadow rounded-lg border border-gray-200">
    <div class="p-4 border-b border-gray-200">
      <h3 class="text-lg font-semibold text-gray-700">
        <i class="fas fa-list-ul text-gray-700"></i>
        لیست تنظیمات ثبت‌شده
        
      </h3>
    </div>

    <!-- حذف overflow-x-auto برای جلوگیری از اسکرول افقی -->
  <div class="flex justify-center">
  <table class="min-w-full w-full text-center text-gray-700 text-sm">
    <thead class="bg-gray-100 font-bold">
      <tr>
        <th class="py-3 px-6 whitespace-nowrap text-center">ردیف</th>
        <th class="py-3 px-6 whitespace-nowrap text-center">نام نظرسنجی</th>
        <th class="py-3 px-6 whitespace-nowrap text-center">شماره صف</th>
        <th class="py-3 px-6 whitespace-nowrap text-center">تعداد خطای مجاز</th>
        <th class="py-3 px-6 whitespace-nowrap text-center">حداقل امتیاز</th>
        <th class="py-3 px-6 whitespace-nowrap text-center">حداکثر امتیاز</th>
        <th class="py-3 px-6 whitespace-nowrap text-center">وضعیت</th>
        <th class="py-3 px-6 whitespace-nowrap text-center">عملیات</th>
      </tr>
    </thead>
    <tbody class="divide-y divide-gray-200">
      <!-- ردیف‌ها -->
      <?php if ($result->num_rows == 0): ?>
        <tr>
          <td colspan="8" class="text-center py-6 text-gray-500">هیچ تنظیماتی ثبت نشده است.</td>
        </tr>
      <?php else: 
        $counter = 1;
        while ($row = $result->fetch_assoc()):
      ?>
        <tr class="hover:bg-gray-50">
          <td class="py-3 px-6 text-center"><?= $counter++ ?></td>
          <td class="py-3 px-6 text-center"><?= htmlspecialchars($row['survey_name']) ?></td>
          <td class="py-3 px-6 text-center"><?= htmlspecialchars($row['queue']) ?></td>
          <td class="py-3 px-6 text-center"><?= htmlspecialchars($row['max_invalid']) ?></td>
          <td class="py-3 px-6 text-center">
              <?php
              if ($row['min_score_record'] == 'disabled' || $row['min_score_record'] == 0) {
                  echo 'غیرفعال';
              } else {
                  echo htmlspecialchars($row['min_score_record']);
              }
              ?>
              </td>


          <td class="py-3 px-6 text-center">
            <?php
            if ($row['max_score_record'] == 0) {
                echo 'غیرفعال';
            } else {
                echo htmlspecialchars($row['max_score_record']);
            }
            ?>
            </td>

          <td class="py-3 px-6 text-center">
            <?php if ($row['status'] == "active"): ?>
              <span class="inline-block bg-green-100 text-green-800 px-3 py-1 rounded-full text-xs font-semibold">فعال</span>
            <?php else: ?>
              <span class="inline-block bg-red-100 text-red-800 px-3 py-1 rounded-full text-xs font-semibold">غیرفعال</span>
            <?php endif; ?>
          </td>
          <td class="py-3 px-6 text-center space-x-3 space-x-reverse flex justify-center">
            <a href="edit_settings.php?id=<?= $row['id'] ?>" class="text-yellow-500 hover:text-yellow-700" title="ویرایش">
              <i class="fas fa-edit text-lg"></i>
            </a>
            <a href="#" 
                onclick="deleteSetting('<?= $row['id'] ?>', '<?= urlencode($row['queue']) ?>'); return false;" 
                class="text-red-600 hover:text-red-800" 
                title="حذف">
                  <i class="fas fa-trash text-lg"></i>
              </a>
          </td>
        </tr>
      <?php endwhile; endif; ?>
    </tbody>
  </table>
</div>


    <div class="p-3 border-t border-gray-200 text-center text-sm text-gray-500">
      سامانه نظرسنجی - Rayan VoIP
    </div>
  </div>
</div>
<script>
function deleteSetting(id, queue) {
    Swal.fire({
        title: 'آیا مطمئن هستید؟',
        text: "این عملیات قابل بازگشت نیست!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'بله، حذف شود!',
        cancelButtonText: 'لغو'
    }).then((result) => {
        if (result.isConfirmed) {
            // هدایت به صفحه حذف
            window.location.href = `delete_settings.php?id=${id}&queue=${queue}`;
        }
    });
}
</script>

<?php
if (file_exists('./../../footer.php')) {
    include './../../footer.php';
} else {
    die("فایل footer.php پیدا نشد.");
}
?>
