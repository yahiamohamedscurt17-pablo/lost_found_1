<?php
require_once 'config.php';
require_login();
$error = '';
$relatedId = filter_input(INPUT_GET, 'related_report', FILTER_VALIDATE_INT) ?: (filter_input(INPUT_POST, 'related_report_id', FILTER_VALIDATE_INT) ?: null);
$relatedReport = null;
if ($relatedId) {
  $rel = $pdo->prepare("SELECT id, item_name, user_id FROM reports WHERE id=? AND report_type='lost'");
  $rel->execute([$relatedId]); $relatedReport = $rel->fetch();
  if (!$relatedReport) $relatedId = null;
}
$categories = ['إلكترونيات','أدوات مدرسية','ملابس','بطاقات ومحافظ','مفاتيح','كتب وكراسات','أخرى'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  verify_csrf();
  $type = $_POST['report_type'] ?? '';
  $name = trim($_POST['item_name'] ?? '');
  $category = $_POST['category'] ?? '';
  $description = trim($_POST['description'] ?? '');
  $location = trim($_POST['location'] ?? '');
  $date = $_POST['event_date'] ?? '';
  if (!in_array($type,['lost','found'],true)) $error='اختار نوع البلاغ.';
  elseif (mb_strlen($name)<2 || mb_strlen($name)>140) $error='اسم الغرض مطلوب.';
  elseif (!in_array($category,$categories,true)) $error='اختار التصنيف.';
  elseif (mb_strlen($description)<10) $error='اكتب وصفًا لا يقل عن 10 أحرف.';
  elseif ($location==='') $error='اكتب المكان.';
  elseif (!$date || !preg_match('/^\d{4}-\d{2}-\d{2}$/',$date)) $error='اختار التاريخ.';
  else {
    $imagePath = null;
    if (!empty($_FILES['item_image']['name'])) {
      if ($_FILES['item_image']['error'] !== UPLOAD_ERR_OK) $error='تعذر رفع الصورة.';
      elseif ($_FILES['item_image']['size'] > 3*1024*1024) $error='حجم الصورة يجب ألا يتجاوز 3 ميجابايت.';
      else {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($_FILES['item_image']['tmp_name']);
        $allowed = ['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];
        if (!isset($allowed[$mime])) $error='الصورة يجب أن تكون JPG أو PNG أو WEBP.';
        else {
          $dir = __DIR__.'/uploads';
          if (!is_dir($dir)) mkdir($dir,0755,true);
          $filename = bin2hex(random_bytes(16)).'.'.$allowed[$mime];
          if (move_uploaded_file($_FILES['item_image']['tmp_name'],$dir.'/'.$filename)) $imagePath='uploads/'.$filename;
          else $error='فشل حفظ الصورة.';
        }
      }
    }
    if (!$error) {
      if ($relatedId && $type !== 'found') $error='البلاغ المرتبط يجب أن يكون من نوع غرض تم العثور عليه.';
      if (!$error) {
        $stmt=$pdo->prepare('INSERT INTO reports (user_id,report_type,item_name,category,description,location,event_date,image_path,related_report_id) VALUES (?,?,?,?,?,?,?,?,?)');
        $stmt->execute([current_user_id(),$type,$name,$category,$description,$location,$date,$imagePath,($type==='found' ? $relatedId : null)]);
      }
      if (!$error) { set_flash($relatedId ? 'تم نشر بلاغ العثور وربطه بالبلاغ المفقود.' : 'تم نشر البلاغ بنجاح.');
      header('Location: report.php?id='.$pdo->lastInsertId()); exit; }
    }
  }
}
header_nav('إضافة بلاغ');
?>
<div class="page-intro"><span class="eyebrow">NEW REPORT</span><h1>إضافة بلاغ جديد</h1><p>كلما كانت التفاصيل واضحة، زادت فرصة العثور على الغرض.</p></div>
<div class="form-card wide-form">
<?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>
<form method="post" enctype="multipart/form-data" class="form-stack"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><?php if ($relatedId && $relatedReport): ?><input type="hidden" name="related_report_id" value="<?= (int)$relatedId ?>"><div class="notice-box">أنت بتبلغ عن العثور على الغرض المرتبط بالبلاغ: <strong><?= e($relatedReport['item_name']) ?></strong> (#<?= (int)$relatedId ?>).</div><?php endif; ?>
<div class="choice-row"><label class="choice-card"><input type="radio" name="report_type" value="lost" <?= (($_POST['report_type'] ?? 'lost')==='lost')?'checked':'' ?>><span class="choice-symbol">⌕</span><b>غرض مفقود</b><small>حاجة ضاعت منك</small></label><label class="choice-card"><input type="radio" name="report_type" value="found" <?= (($_POST['report_type'] ?? '')==='found')?'checked':'' ?>><span class="choice-symbol">✓</span><b>غرض تم العثور عليه</b><small>حاجة لقيتها</small></label></div>
<div class="form-two-col"><label>اسم الغرض<input name="item_name" required maxlength="140" value="<?= e($_POST['item_name'] ?? (($relatedId && $relatedReport) ? $relatedReport['item_name'] : '')) ?>" placeholder="مثال: علبة سماعات سوداء"></label><label>التصنيف<select name="category" required><option value="">اختار التصنيف</option><?php foreach($categories as $c): ?><option <?= (($_POST['category'] ?? '')===$c)?'selected':'' ?>><?= e($c) ?></option><?php endforeach; ?></select></label></div>
<label>الوصف التفصيلي<textarea name="description" rows="4" required minlength="10" placeholder="اكتب اللون أو العلامات المميزة أو أي تفاصيل تساعد في التعرف على الغرض."><?= e($_POST['description'] ?? '') ?></textarea></label>
<div class="form-two-col"><label>المكان<input name="location" required maxlength="180" value="<?= e($_POST['location'] ?? '') ?>" placeholder="مثال: معمل الدور الثاني"></label><label>التاريخ<input type="date" name="event_date" required value="<?= e($_POST['event_date'] ?? date('Y-m-d')) ?>"></label></div>
<label class="upload-field">صورة الغرض (اختياري)<input type="file" name="item_image" accept="image/jpeg,image/png,image/webp"><small>JPG أو PNG أو WEBP — بحد أقصى 3 ميجابايت.</small></label>
<div class="form-actions"><a class="btn btn-light" href="index.php">إلغاء</a><button class="btn btn-primary" type="submit">نشر البلاغ ←</button></div>
</form></div>
<?php footer_nav(); ?>
