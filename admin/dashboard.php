<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../middleware/auth.php';
require_login('admin');
$schoolId = current_school_id();
$counts = [
  'teachers' => db()->prepare('SELECT COUNT(*) c FROM teachers WHERE school_id=? AND active=1'),
  'students' => db()->prepare('SELECT COUNT(*) c FROM students WHERE school_id=? AND active=1'),
  'updates' => db()->prepare('SELECT COUNT(*) c FROM updates WHERE school_id=?'),
];
foreach($counts as $k=>$st){$st->execute([$schoolId]);$counts[$k]=$st->fetch()['c'];}
include __DIR__ . '/../views/layout/header.php'; include __DIR__ . '/../views/layout/sidebar.php'; include __DIR__ . '/../views/layout/topbar.php';
?>
<div class="row g-3">
<?php foreach($counts as $k=>$v): ?><div class="col-md-4"><div class="card p-3"><h6 class="text-muted text-capitalize"><?=e($k)?></h6><h2><?=$v?></h2></div></div><?php endforeach; ?>
</div>
<?php include __DIR__ . '/../views/layout/footer.php'; ?>
