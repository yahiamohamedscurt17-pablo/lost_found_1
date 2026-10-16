<?php
require_once 'config.php';
require_login();
$stmt=$pdo->prepare("SELECT * FROM reports WHERE user_id=? ORDER BY created_at DESC");
$stmt->execute([current_user_id()]);$reports=$stmt->fetchAll();
$statuses=['open'=>'مفتوح','matched'=>'تطابق محتمل','returned'=>'تم الإرجاع','closed'=>'مغلق'];
header_nav('بلاغاتي');
?>
<div class="page-intro"><span class="eyebrow">MY ACTIVITY</span><h1>بلاغاتي</h1><p>تابع البلاغات التي أضفتها وحالة كل بلاغ.</p><a class="btn btn-primary" href="create_report.php">+ إضافة بلاغ</a></div>
<?php if(!$reports): ?><div class="empty-state"><div class="empty-icon">＋</div><h3>لسه ما أضفتش بلاغات</h3><p>أضف أول بلاغ وساعد في إرجاع المفقودات لأصحابها.</p><a class="btn btn-primary" href="create_report.php">إضافة بلاغ</a></div><?php else: ?><div class="table-wrap"><table class="data-table"><thead><tr><th>الغرض</th><th>النوع</th><th>التاريخ</th><th>الحالة</th><th></th></tr></thead><tbody><?php foreach($reports as $r): ?><tr><td><strong><?= e($r['item_name']) ?></strong><small><?= e($r['category']) ?></small></td><td><?= $r['report_type']==='lost'?'مفقود':'تم العثور عليه' ?></td><td><?= e($r['event_date']) ?></td><td><span class="status-pill <?= e($r['status']) ?>"><?= e($statuses[$r['status']] ?? $r['status']) ?></span></td><td><a class="text-link" href="report.php?id=<?= (int)$r['id'] ?>">التفاصيل ←</a></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?>
<?php footer_nav(); ?>
