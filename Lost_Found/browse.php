<?php
require_once 'config.php';
$type = $_GET['type'] ?? '';
$class = $_GET['class_code'] ?? '';
$q = trim($_GET['q'] ?? '');
$allowedTypes = ['lost','found'];
$sql = "SELECT r.*,u.full_name,u.class_code FROM reports r JOIN users u ON u.id=r.user_id WHERE r.status='open'";
$params=[];
if (in_array($type,$allowedTypes,true)) { $sql.=" AND r.report_type=?"; $params[]=$type; }
if (in_array($class,class_options(),true)) { $sql.=" AND u.class_code=?"; $params[]=$class; }
if ($q!=='') { $sql.=" AND (r.item_name LIKE ? OR r.description LIKE ? OR r.location LIKE ? OR r.category LIKE ?)"; $like='%'.$q.'%'; array_push($params,$like,$like,$like,$like); }
$sql.=" ORDER BY r.created_at DESC";
$stmt=$pdo->prepare($sql);$stmt->execute($params);$reports=$stmt->fetchAll();
header_nav('تصفح البلاغات');
?>
<div class="page-intro"><span class="eyebrow">EXPLORE REPORTS</span><h1>تصفح البلاغات</h1><p>استخدم البحث والفلاتر للوصول إلى الغرض الذي تبحث عنه.</p></div>
<form class="filter-panel" method="get"><div class="search-input"><span>⌕</span><input name="q" value="<?= e($q) ?>" placeholder="ابحث باسم الغرض أو الوصف أو المكان..."></div><select name="type"><option value="">كل أنواع البلاغات</option><option value="lost" <?= $type==='lost'?'selected':'' ?>>مفقود</option><option value="found" <?= $type==='found'?'selected':'' ?>>تم العثور عليه</option></select><select name="class_code"><option value="">كل الفصول</option><?php foreach(class_options() as $c): ?><option value="<?= e($c) ?>" <?= $class===$c?'selected':'' ?>><?= e($c) ?></option><?php endforeach; ?></select><button class="btn btn-primary" type="submit">بحث</button></form>
<div class="results-line"><span>تم العثور على <b><?= count($reports) ?></b> بلاغ</span><a href="browse.php">مسح الفلاتر</a></div>
<?php if (!$reports): ?><div class="empty-state"><div class="empty-icon">⌕</div><h3>مفيش نتائج مطابقة</h3><p>جرّب كلمات بحث مختلفة أو غيّر الفلاتر.</p></div><?php else: ?><div class="report-grid">
<?php foreach($reports as $r): ?><article class="report-card"><div class="report-image"><?php if($r['image_path']): ?><img src="<?= e($r['image_path']) ?>" alt="<?= e($r['item_name']) ?>"><?php else: ?><span><?= $r['report_type']==='lost'?'⌕':'✓' ?></span><?php endif; ?><label class="type-tag <?= e($r['report_type']) ?>"><?= $r['report_type']==='lost'?'مفقود':'تم العثور عليه' ?></label></div><div class="report-body"><div class="card-meta"><?= e($r['category']) ?> <span>•</span> فصل <?= e($r['class_code'] ?: 'غير محدد') ?></div><h3><?= e($r['item_name']) ?></h3><p><?= e(mb_strimwidth($r['description'],0,105,'…','UTF-8')) ?></p><div class="card-location">⌖ <?= e($r['location']) ?></div><a class="card-link" href="report.php?id=<?= (int)$r['id'] ?>">عرض التفاصيل <span>←</span></a></div></article><?php endforeach; ?>
</div><?php endif; ?>
<?php footer_nav(); ?>
