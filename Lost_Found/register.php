<?php
require_once 'config.php';
if (is_logged_in()) { header('Location: ' . dashboard_url()); exit; }
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  verify_csrf();
  $name = trim($_POST['full_name'] ?? '');
  $email = strtolower(trim($_POST['email'] ?? ''));
  $password = $_POST['password'] ?? '';
  $role = $_POST['role'] ?? 'student';
  $class = $_POST['class_code'] ?? '';
  $adminCode = trim($_POST['admin_code'] ?? '');
  if (mb_strlen($name) < 3) $error = 'اكتب الاسم بالكامل (3 أحرف على الأقل).';
  elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $error = 'اكتب بريدًا إلكترونيًا صحيحًا.';
  elseif (strlen($password) < 8) $error = 'كلمة المرور لازم تكون 8 أحرف على الأقل.';
  elseif (!in_array($role, ['student', 'admin'], true)) $error = 'اختار نوع الحساب الصحيح.';
  elseif ($role === 'student' && !in_array($class, class_options(), true)) $error = 'اختار فصلك من القائمة.';
  elseif ($role === 'admin' && !hash_equals(ADMIN_REGISTRATION_CODE, $adminCode)) $error = 'رمز الإدارة غير صحيح. اطلب الرمز من مسؤول المدرسة.';
  else {
    try {
      $stmt = $pdo->prepare('INSERT INTO users (full_name,email,password_hash,class_code,role) VALUES (?,?,?,?,?)');
      $stmt->execute([$name,$email,password_hash($password,PASSWORD_DEFAULT),$role === 'student' ? $class : null,$role]);
      set_flash('تم إنشاء حسابك بنجاح. سجّل الدخول للمتابعة.');
      header('Location: login.php'); exit;
    } catch (PDOException $e) {
      $error = $e->getCode() === '23000' ? 'البريد الإلكتروني مستخدم بالفعل.' : 'حصل خطأ أثناء إنشاء الحساب.';
    }
  }
}
header_nav('إنشاء حساب');
?>
<section class="auth-layout"><div class="auth-aside"><span class="eyebrow">JOIN YOUR SCHOOL COMMUNITY</span><h1>خليك جزء من<br><span>الحل.</span></h1><p>حساب واحد يساعدك تتابع بلاغاتك وتوصل للمفقودات بشكل أسهل.</p><div class="auth-bullets"><div>✓ حساب مناسب لدورك في المدرسة</div><div>✓ تابع البلاغات بسهولة</div><div>✓ ساعد في إرجاع المفقودات</div></div></div>
<div class="form-card"><div class="form-heading"><span class="form-icon">＋</span><h2>إنشاء حساب</h2><p>اختار نوع الحساب واملأ البيانات التالية.</p></div>
<?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>
<form method="post" class="form-stack"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
<label>نوع الحساب<select name="role" id="account-role" required><option value="student" <?= (($_POST['role'] ?? 'student')==='student')?'selected':'' ?>>طالب</option><option value="admin" <?= (($_POST['role'] ?? '')==='admin')?'selected':'' ?>>إدارة المدرسة</option></select></label>
<label>الاسم بالكامل<input name="full_name" required minlength="3" maxlength="120" value="<?= e($_POST['full_name'] ?? '') ?>" placeholder="اكتب اسمك هنا"></label>
<label>البريد الإلكتروني<input type="email" name="email" required maxlength="190" value="<?= e($_POST['email'] ?? '') ?>" placeholder="name@example.com"></label>
<div id="student-class-field"><label>الفصل الدراسي<select name="class_code" id="class-code"><option value="">اختار الفصل</option><?php foreach(class_options() as $c): ?><option value="<?= e($c) ?>" <?= (($_POST['class_code'] ?? '')===$c)?'selected':'' ?>><?= e($c) ?></option><?php endforeach; ?></select></label></div>
<div id="admin-code-field"><label>رمز الإدارة<input type="password" name="admin_code" id="admin-code" autocomplete="off" placeholder="اطلب الرمز من مسؤول المدرسة"></label><small class="form-bottom">رمز الإدارة مطلوب حتى لا يتمكن أي شخص من إنشاء حساب إداري.</small></div>
<label>كلمة المرور<input type="password" name="password" required minlength="8" placeholder="8 أحرف على الأقل"></label>
<button class="btn btn-primary btn-block" type="submit">إنشاء الحساب</button>
<p class="form-bottom">عندك حساب بالفعل؟ <a href="login.php">سجّل الدخول</a></p></form></div></section>
<?php footer_nav(); ?>
