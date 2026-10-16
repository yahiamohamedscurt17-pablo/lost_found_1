<?php
require_once 'config.php';
if (is_logged_in()) { header('Location: ' . dashboard_url()); exit; }
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  verify_csrf();
  $email = strtolower(trim($_POST['email'] ?? ''));
  $password = $_POST['password'] ?? '';
  $stmt = $pdo->prepare('SELECT id,full_name,email,password_hash,class_code,role FROM users WHERE email=? LIMIT 1');
  $stmt->execute([$email]); $user = $stmt->fetch();
  if ($user && password_verify($password, $user['password_hash'])) {
    session_regenerate_id(true);
    $_SESSION['user_id'] = (int)$user['id'];
    $_SESSION['user_name'] = $user['full_name'];
    $_SESSION['user_class'] = $user['class_code'];
    $_SESSION['role'] = $user['role'];
    set_flash('أهلًا بيك، تم تسجيل الدخول بنجاح.');
    header('Location: ' . dashboard_url()); exit;
  } else $error = 'البريد الإلكتروني أو كلمة المرور غير صحيحة.';
}
header_nav('تسجيل الدخول');
?>
<section class="auth-layout"><div class="auth-aside"><span class="eyebrow">WELCOME BACK</span><h1>أهلًا بعودتك<br><span>للمجتمع.</span></h1><p>سجّل دخولك لمتابعة بلاغاتك أو الإبلاغ عن غرض مفقود أو تم العثور عليه.</p><div class="auth-bullets"><div>✓ منصة منظمة وسهلة</div><div>✓ بلاغات محدثة</div><div>✓ فصول المدرسة في مكان واحد</div></div></div>
<div class="form-card"><div class="form-heading"><span class="form-icon">↗</span><h2>تسجيل الدخول</h2><p>اكتب بيانات حسابك.</p></div>
<?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>
<form method="post" class="form-stack"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
<label>البريد الإلكتروني<input type="email" name="email" required value="<?= e($_POST['email'] ?? '') ?>" placeholder="name@example.com"></label>
<label>كلمة المرور<input type="password" name="password" required placeholder="اكتب كلمة المرور"></label>
<button class="btn btn-primary btn-block" type="submit">تسجيل الدخول</button>
<p class="form-bottom">لسه معندكش حساب؟ <a href="register.php">إنشاء حساب جديد</a></p></form></div></section>
<?php footer_nav(); ?>
