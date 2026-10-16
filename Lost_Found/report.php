<?php
require_once 'config.php';
$id = filter_input(INPUT_GET,'id',FILTER_VALIDATE_INT);
if (!$id) { http_response_code(404); header_nav('البلاغ غير موجود'); echo '<div class="empty-state"><h2>البلاغ غير موجود</h2><a href="browse.php">العودة للبلاغات</a></div>'; footer_nav(); exit; }
$stmt=$pdo->prepare("SELECT r.*,u.full_name,u.email,u.class_code FROM reports r JOIN users u ON u.id=r.user_id WHERE r.id=?");
$stmt->execute([$id]);$r=$stmt->fetch();
if (!$r) { http_response_code(404); header_nav('البلاغ غير موجود'); echo '<div class="empty-state"><h2>البلاغ غير موجود</h2><a href="browse.php">العودة للبلاغات</a></div>'; footer_nav(); exit; }
$own = is_logged_in() && current_user_id()===(int)$r['user_id'];
if ($_SERVER['REQUEST_METHOD']==='POST' && is_logged_in()) {
  verify_csrf();
  if (($own || is_admin()) && isset($_POST['status'])) {
    $status=$_POST['status'];
    if (in_array($status,['open','matched','returned','closed'],true)) {
      $u=$pdo->prepare('UPDATE reports SET status=? WHERE id=?');$u->execute([$status,$id]);
      set_flash('تم تحديث حالة البلاغ.');header('Location: report.php?id='.$id);exit;
    }
  } elseif (!$own && isset($_POST['message'])) {
    $message=trim($_POST['message']);
    if ($message!=='') {
      $m=$pdo->prepare('INSERT INTO messages (report_id,sender_id,message_text) VALUES (?,?,?)');
      $m->execute([$id,current_user_id(),mb_substr($message,0,1500)]);
      set_flash('تم إرسال رسالتك لصاحب البلاغ.');header('Location: report.php?id='.$id);exit;
    }
  }
}
$messages=[];
if ($own || is_admin()) {
  $m=$pdo->prepare('SELECT m.*,u.full_name FROM messages m JOIN users u ON u.id=m.sender_id WHERE m.report_id=? ORDER BY m.created_at DESC');$m->execute([$id]);$messages=$m->fetchAll();
}
$statuses=['open'=>'مفتوح','matched'=>'تم العثور على تطابق','returned'=>'تم إرجاع الغرض','closed'=>'مغلق'];
header_nav('تفاصيل البلاغ');
?>
<div class="detail-back"><a href="browse.php">→ العودة للبلاغات</a></div>
<div class="detail-layout"><div class="detail-visual"><?php if($r['image_path']): ?><img src="<?= e($r['image_path']) ?>" alt="<?= e($r['item_name']) ?>"><?php else: ?><div class="placeholder-art"><span><?= $r['report_type']==='lost'?'⌕':'✓' ?></span><small>لا توجد صورة مرفقة</small></div><?php endif; ?></div>
<div class="detail-content"><span class="type-tag inline <?= e($r['report_type']) ?>"><?= $r['report_type']==='lost'?'غرض مفقود':'غرض تم العثور عليه' ?></span><h1><?= e($r['item_name']) ?></h1><div class="status-line"><span class="status-dot <?= e($r['status']) ?>"></span><?= e($statuses[$r['status']] ?? $r['status']) ?></div><p class="detail-description"><?= nl2br(e($r['description'])) ?></p>
<div class="detail-facts"><div><small>التصنيف</small><strong><?= e($r['category']) ?></strong></div><div><small>المكان</small><strong><?= e($r['location']) ?></strong></div><div><small>التاريخ</small><strong><?= e($r['event_date']) ?></strong></div><div><small>فصل صاحب البلاغ</small><strong><?= e($r['class_code'] ?: 'غير محدد') ?></strong></div></div>
<div class="owner-box"><span class="owner-avatar"><?= e(mb_substr($r['full_name'],0,1)) ?></span><div><small>صاحب البلاغ</small><strong><?= e($r['full_name']) ?></strong></div><?php if($own): ?><span class="you-tag">بلاغك</span><?php endif; ?></div>
<?php if ($r['report_type']==='lost' && is_logged_in() && !$own && $r['status']!=='returned' && $r['status']!=='closed'): ?><div class="notice-box found-action-box"><strong>لقيت الحاجة دي؟</strong><p>بلّغ صاحب الحاجة عن مكان العثور عليها، وهيتم ربط البلاغين ببعض.</p><a class="btn btn-primary" href="create_report.php?report_type=found&amp;related_report=<?= (int)$r['id'] ?>">✓ لقيت الحاجة دي</a></div><?php endif; ?>
<?php if ($r['report_type']==='lost'): $linked=$pdo->prepare("SELECT id,item_name,location,event_date,status FROM reports WHERE related_report_id=? AND report_type='found' ORDER BY created_at DESC"); $linked->execute([$id]); $linkedReports=$linked->fetchAll(); if ($linkedReports): ?><section class="messages-section"><h2>بلاغات العثور المرتبطة</h2><?php foreach($linkedReports as $lr): ?><div class="message-card"><strong><a href="report.php?id=<?= (int)$lr['id'] ?>"><?= e($lr['item_name']) ?> — بلاغ #<?= (int)$lr['id'] ?></a></strong><p>مكان العثور: <?= e($lr['location']) ?> · التاريخ: <?= e($lr['event_date']) ?></p></div><?php endforeach; ?></section><?php endif; endif; ?>
<?php if (is_logged_in() && !$own): ?><form method="post" class="message-form"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><label>رسالة لصاحب البلاغ<textarea name="message" rows="3" required maxlength="1500" placeholder="اكتب رسالة محترمة فيها التفاصيل اللازمة..."></textarea></label><button class="btn btn-primary" type="submit">إرسال رسالة</button></form><?php elseif(!is_logged_in()): ?><div class="notice-box">سجّل الدخول للتواصل مع صاحب البلاغ. <a href="login.php">تسجيل الدخول</a></div><?php endif; ?>
<?php if (($own || is_admin())): ?><form method="post" class="status-form"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><label>تحديث حالة البلاغ<select name="status"><?php foreach($statuses as $key=>$label): ?><option value="<?= e($key) ?>" <?= $r['status']===$key?'selected':'' ?>><?= e($label) ?></option><?php endforeach; ?></select></label><button class="btn btn-light" type="submit">حفظ الحالة</button></form><?php endif; ?>
</div></div>
<?php if(($own || is_admin()) && $messages): ?><section class="messages-section"><h2>الرسائل الواردة</h2><?php foreach($messages as $m): ?><div class="message-card"><div><strong><?= e($m['full_name']) ?></strong><small><?= e($m['created_at']) ?></small></div><p><?= nl2br(e($m['message_text'])) ?></p></div><?php endforeach; ?></section><?php endif; ?>
<?php footer_nav(); ?>
