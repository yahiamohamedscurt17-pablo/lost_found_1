<?php
require_once 'config.php';
header_nav('الرئيسية');
$stmt = $pdo->query("SELECT r.*, u.full_name, u.class_code FROM reports r JOIN users u ON u.id=r.user_id WHERE r.status='open' ORDER BY r.created_at DESC LIMIT 6");
$latest = $stmt->fetchAll();
$stats = $pdo->query("SELECT COUNT(*) total, SUM(status='returned') returned FROM reports")->fetch();
?>
<section class="hero">
  <div class="hero-content">
    <span class="eyebrow">WE SCHOOLS • STUDENT COMMUNITY</span>
    <h1>حاجتك المفقودة<br><span>ممكن ترجعلك.</span></h1>
    <p>منصة واحدة للإبلاغ عن الأشياء المفقودة أو التي تم العثور عليها داخل المدرسة، ومساعدة الطلاب على الوصول لبعضهم بشكل منظم.</p>
    <div class="hero-actions">
      <a class="btn btn-primary" href="<?= is_logged_in() ? 'create_report.php' : 'register.php' ?>">+ أضف بلاغًا جديدًا</a>
      <a class="btn btn-light" href="browse.php">تصفح البلاغات</a>
    </div>
    <div class="hero-note"><span class="dot"></span> البحث والتصفح متاحان للجميع، وإضافة البلاغات تتطلب حسابًا.</div>
  </div>
  <div class="hero-art" aria-hidden="true">
    <div class="orb orb-one"></div><div class="orb orb-two"></div>
    <div class="search-illustration"><div class="search-glass">⌕</div><div class="backpack">▣<span>✓</span></div><div class="spark s1">✦</div><div class="spark s2">✧</div></div>
    <div class="floating-card"><span class="mini-icon">✓</span><div><b>تم العثور على غرض</b><small>خلّيه يرجع لصاحبه</small></div></div>
  </div>
</section>
<section class="stats-grid">
  <div class="stat-card"><span class="stat-icon purple">⌕</span><div><strong><?= (int)($stats['total'] ?? 0) ?></strong><small>إجمالي البلاغات</small></div></div>
  <div class="stat-card"><span class="stat-icon green">✓</span><div><strong><?= (int)($stats['returned'] ?? 0) ?></strong><small>أغراض تم إرجاعها</small></div></div>
  <div class="stat-card"><span class="stat-icon orange">18</span><div><strong>فصلًا</strong><small>اختيارات الفصول من A1 إلى C6</small></div></div>
</section>
<section class="section-heading"><div><span class="eyebrow">LATEST REPORTS</span><h2>أحدث البلاغات</h2><p>شوف إذا كان غرضك موجودًا ضمن البلاغات الأخيرة.</p></div><a class="text-link" href="browse.php">عرض كل البلاغات ←</a></section>
<?php if (!$latest): ?>
  <div class="empty-state"><div class="empty-icon">⌕</div><h3>لسه مفيش بلاغات</h3><p>كن أول شخص يضيف بلاغًا ويساعد مجتمع المدرسة.</p><a class="btn btn-primary" href="<?= is_logged_in() ? 'create_report.php' : 'register.php' ?>">ابدأ الآن</a></div>
<?php else: ?>
<div class="report-grid">
<?php foreach ($latest as $r): ?>
  <article class="report-card">
    <div class="report-image"><?php if ($r['image_path']): ?><img src="<?= e($r['image_path']) ?>" alt="<?= e($r['item_name']) ?>"><?php else: ?><span><?= $r['report_type']==='lost'?'⌕':'✓' ?></span><?php endif; ?><label class="type-tag <?= e($r['report_type']) ?>"><?= $r['report_type']==='lost'?'مفقود':'تم العثور عليه' ?></label></div>
    <div class="report-body"><div class="card-meta"><?= e($r['category']) ?> <span>•</span> <?= e($r['class_code'] ?: 'فصل غير محدد') ?></div><h3><?= e($r['item_name']) ?></h3><p><?= e(mb_strimwidth($r['description'],0,110,'…','UTF-8')) ?></p><div class="card-location">⌖ <?= e($r['location']) ?></div><a class="card-link" href="report.php?id=<?= (int)$r['id'] ?>">عرض التفاصيل <span>←</span></a></div>
  </article>
<?php endforeach; ?>
</div>
<?php endif; ?>
<section class="how-section"><div class="section-heading"><div><span class="eyebrow">HOW IT WORKS</span><h2>ثلاث خطوات بسيطة</h2></div></div><div class="steps-grid"><div class="step"><span>01</span><h3>أنشئ حسابك</h3><p>اكتب اسمك وبريدك وحدد فصلك من القائمة.</p></div><div class="step"><span>02</span><h3>أضف بلاغًا</h3><p>حدد نوع البلاغ، ووصف الغرض ومكانه وتاريخه.</p></div><div class="step"><span>03</span><h3>ساعد في إرجاعه</h3><p>راجع التفاصيل وتواصل من خلال بيانات صاحب البلاغ.</p></div></div></section>
<?php footer_nav(); ?>
