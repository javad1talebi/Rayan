<?php
// صفحه تولید لایسنس سمت فروشنده

$license = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $request_code = strtoupper(trim($_POST['request_code'] ?? ''));

    // بررسی معتبر بودن ورودی
    if (strlen($request_code) !== 10) {
        $error = "کد درخواستی باید 10 رقمی باشد.";
    } else {
        $secret = 'MY_PRIVATE_SECRET_2025'; // کلید خصوصی فقط نزد فروشنده
        $license = substr(hash_hmac('sha256', $request_code, $secret), 0, 12);
    }
}
?>

<!DOCTYPE html>
<html lang="fa">
<head>
  <meta charset="UTF-8">
  <title>تولید لایسنس</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">

  <div class="bg-white p-8 rounded-2xl shadow-xl w-full max-w-md">
    <h2 class="text-xl font-bold text-gray-700 mb-6 text-center">تولید لایسنس برای مشتری</h2>
    <form method="post" class="space-y-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">کد سخت‌افزار (۱۰ رقمی)</label>
        <input type="text" name="request_code" maxlength="10" required
               class="w-full px-4 py-2 border rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500"
               placeholder="مثال: AB12CD34EF">
      </div>
      <button type="submit"
              class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-2 rounded-xl transition">
        تولید لایسنس
      </button>
    </form>

    <?php if (!empty($license)): ?>
      <div class="mt-6 bg-green-100 text-green-800 px-4 py-3 rounded-xl text-center font-mono">
        کد لایسنس: <br><strong><?= htmlspecialchars($license) ?></strong>
      </div>
    <?php elseif (!empty($error)): ?>
      <div class="mt-6 bg-red-100 text-red-700 px-4 py-3 rounded-xl text-center">
        <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>
  </div>

</body>
</html>
