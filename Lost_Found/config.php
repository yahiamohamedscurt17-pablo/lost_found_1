<?php
declare(strict_types=1);

$host = 'localhost';
$dbname = 'lost111';
// غيّر هذا الرمز إلى رمز خاص بالمدرسة قبل استخدام الموقع فعليًا.
const ADMIN_REGISTRATION_CODE = 'LOST111-ADMIN-2026';
$username = 'root';
$password = '';

$dsn = "mysql:host={$host};dbname={$dbname};charset=utf8mb4";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, $username, $password, $options);
} catch (PDOException $e) {
    http_response_code(500);
    exit('تعذر الاتصال بقاعدة البيانات. تأكد من تشغيل MySQL واستيراد database.sql ثم راجع config.php.');
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function e(?string $value): string {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}
function is_logged_in(): bool {
    return isset($_SESSION['user_id']);
}
function current_user_id(): ?int {
    return isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
}
function require_login(): void {
    if (!is_logged_in()) {
        header('Location: login.php');
        exit;
    }
}
function is_admin(): bool {
    return ($_SESSION['role'] ?? '') === 'admin';
}
function dashboard_url(): string {
    return is_admin() ? 'admin.php' : 'create_report.php';
}
function set_flash(string $message, string $type = 'success'): void {
    $_SESSION['flash'] = ['message' => $message, 'type' => $type];
}
function get_flash(): ?array {
    if (!isset($_SESSION['flash'])) return null;
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $flash;
}
function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf_token'];
}
function verify_csrf(): void {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $sent = $_POST['csrf_token'] ?? '';
        if (!is_string($sent) || !hash_equals(csrf_token(), $sent)) {
            http_response_code(419);
            exit('انتهت صلاحية الطلب. ارجع للصفحة وحاول مرة أخرى.');
        }
    }
}
function class_options(): array {
    return ['A1','A2','A3','A4','A5','A6','B1','B2','B3','B4','B5','B6','C1','C2','C3','C4','C5','C6'];
}
function header_nav(string $title = 'المفقودات والمعثورات'): void {
    $userName = $_SESSION['user_name'] ?? '';
    $flash = get_flash();
    ?>
    <!doctype html>
    <html lang="ar" dir="rtl">
    <head>
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <title><?= e($title) ?> | WE Lost & Found</title>
      <link rel="preconnect" href="https://fonts.googleapis.com">
      <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800&display=swap" rel="stylesheet">
      <link rel="stylesheet" href="assets/style.css">
    </head>
    <body>
    <header class="topbar">
      <a class="brand" href="index.php" aria-label="الصفحة الرئيسية">
        <span class="brand-mark">WE</span>
        <span class="brand-copy"><strong>Lost & Found</strong><small>منصة المفقودات والمعثورات</small></span>
      </a>
      <button class="menu-toggle" type="button" aria-label="فتح القائمة" onclick="document.querySelector('.nav-links').classList.toggle('open')">☰</button>
      <nav class="nav-links">
        <a href="index.php">الرئيسية</a>
        <a href="browse.php">تصفح البلاغات</a>
        <?php if (is_logged_in()): ?>
          <a href="create_report.php">إضافة بلاغ</a>
          <a href="my_reports.php">بلاغاتي</a>
          <?php if (is_admin()): ?><a href="admin.php">الإدارة</a><?php endif; ?>
          <span class="nav-user"><?= e($userName) ?></span>
          <a class="nav-cta" href="logout.php">تسجيل الخروج</a>
        <?php else: ?>
          <a href="login.php">تسجيل الدخول</a>
          <a class="nav-cta" href="register.php">إنشاء حساب</a>
        <?php endif; ?>
      </nav>
    </header>
    <main class="container">
    <?php if ($flash): ?>
      <div class="alert <?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
    <?php endif; ?>
    <?php
}
function footer_nav(): void {
    ?>
    </main>
    <footer class="footer">
      <div class="footer-brand"><span class="brand-mark small">WE</span><strong>Lost & Found</strong></div>
      <p>منصة مدرسية تساعد الطلاب على الإبلاغ عن المفقودات والمعثورات بطريقة منظمة.</p>
      <small>مشروع تعليمي مستقل — ليس موقعًا رسميًا تابعًا لشركة WE.</small>
    </footer>
    <script src="assets/app.js"></script>
    </body></html>
    <?php
}
