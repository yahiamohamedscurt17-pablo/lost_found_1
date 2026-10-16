<?php
require_once 'config.php';
require_login();
if (!is_admin()) { http_response_code(403); header_nav('غير مسموح'); echo '<div class="empty-state"><h2>ليس لديك صلاحية الدخول لهذه الصفحة.</h2><a href="index.php">الرئيسية</a></div>'; footer_nav(); exit; }
if ($_SERVER['REQUEST_METHOD']==='POST') {
  verify_csrf();
  if (isset($_POST['delete_report'])) {
    $id=(int)$_POST['delete_report'];$stmt=$pdo->prepare('DELETE FROM reports WHERE id=?');$stmt->execute([$id]);set_flash('تم حذف البلاغ.');header('Location: admin.php');exit;
  }
  if (isset($_POST['change_status'])) {
    $id=(int)$_POST['report_id'];$status=$_POST['status']??'open';
    if(in_array($status,['open','matched','returned','closed'],true)){$stmt=$pdo->prepare('UPDATE reports SET status=? WHERE id=?');$stmt->execute([$status,$id]);set_flash('تم تحديث الحالة.');header('Location: admin.php');exit;}
  }
}
$reports=$pdo->query("SELECT r.*,u.full_name,u.email,u.class_code FROM reports r JOIN users u ON u.id=r.user_id ORDER BY r.created_at DESC")->fetchAll();
$users=$pdo->query("SELECT id,full_name,email,class_code,role,created_at FROM users ORDER BY created_at DESC")->fetchAll();
$counts=$pdo->query("SELECT COUNT(*) reports, SUM(status='open') open_count, SUM(status='returned') returned FROM reports")->fetch();
header_nav('لوحة الإدارة');
?>
<div class="page-intro"><span class="eyebrow">ADMIN CONTROL CENTER</span><h1>لوحة الإدارة</h1><p>إدارة البلاغات ومراجعة بيانات الطلاب.</p></div>
<div class="stats-grid admin-stats"><div class="stat-card"><span class="stat-icon purple">▤</span><div><strong><?= (int)$counts['reports'] ?></strong><small>كل البلاغات</small></div></div><div class="stat-card"><span class="stat-icon orange">◷</span><div><strong><?= (int)$counts['open_count'] ?></strong><small>بلاغات مفتوحة</small></div></div><div class="stat-card"><span class="stat-icon green">✓</span><div><strong><?= (int)$counts['returned'] ?></strong><small>تم إرجاعها</small></div></div></div>
<section class="admin-section"><h2>إدارة البلاغات</h2><div class="table-wrap"><table class="data-table"><thead><tr><th>الغرض</th><th>الطالب / الفصل</th><th>النوع</th><th>الحالة</th><th>إجراء</th></tr></thead><tbody><?php foreach($reports as $r): ?><tr><td><a href="report.php?id=<?= (int)$r['id'] ?>"><?= e($r['item_name']) ?></a><small><?= e($r['category']) ?></small></td><td><?= e($r['full_name']) ?><small><?= e($r['class_code'] ?: 'غير محدد') ?></small></td><td><?= $r['report_type']==='lost'?'مفقود':'معثور عليه' ?></td><td><form method="post" class="inline-form"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="report_id" value="<?= (int)$r['id'] ?>"><select name="status"><?php foreach(['open'=>'مفتوح','matched'=>'تطابق','returned'=>'تم الإرجاع','closed'=>'مغلق'] as $k=>$v): ?><option value="<?= e($k) ?>" <?= $r['status']===$k?'selected':'' ?>><?= e($v) ?></option><?php endforeach; ?></select><button class="mini-btn" name="change_status" value="1">حفظ</button></form></td><td><form method="post" onsubmit="return confirm('متأكد من حذف البلاغ؟')"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><button class="mini-btn danger" name="delete_report" value="<?= (int)$r['id'] ?>">حذف</button></form></td></tr><?php endforeach; ?></tbody></table></div></section>
<section class="admin-section"><h2>حسابات الطلاب</h2><div class="table-wrap"><table class="data-table"><thead><tr><th>الاسم</th><th>البريد الإلكتروني</th><th>الفصل</th><th>الصلاحية</th></tr></thead><tbody><?php foreach($users as $u): ?><tr><td><?= e($u['full_name']) ?></td><td><?= e($u['email']) ?></td><td><?= e($u['class_code'] ?: '—') ?></td><td><?= $u['role']==='admin'?'مدير':'طالب' ?></td></tr><?php endforeach; ?></tbody></table></div></section>
<?php footer_nav(); ?>
