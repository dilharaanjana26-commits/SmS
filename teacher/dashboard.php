<?php
require_once __DIR__ . '/../config/db.php'; require_once __DIR__ . '/../middleware/auth.php';
require_login('teacher'); $schoolId=current_school_id(); $uid=auth_user()['id'];
$teacher=db()->prepare('SELECT * FROM teachers WHERE school_id=? AND user_id=?');$teacher->execute([$schoolId,$uid]);$teacher=$teacher->fetch();
$updates=db()->prepare("SELECT * FROM updates WHERE school_id=? AND audience IN ('all','teachers') ORDER BY publish_at DESC LIMIT 10");$updates->execute([$schoolId]);
include __DIR__ . '/../views/layout/header.php'; include __DIR__ . '/../views/layout/sidebar.php'; include __DIR__ . '/../views/layout/topbar.php'; ?>
<div class="card p-3 mb-3"><h5>Teacher Dashboard</h5><p><?=e($teacher['name'] ?? '')?></p></div>
<div class="card p-3"><h6>Admin Updates</h6><?php foreach($updates as $u):?><div class="border-bottom py-2"><b><?=e($u['title'])?></b><div><?=e($u['body'])?></div></div><?php endforeach;?></div>
<?php include __DIR__ . '/../views/layout/footer.php'; ?>
