<?php
require_once __DIR__ . '/../config/db.php'; require_once __DIR__ . '/../middleware/auth.php';
require_login('student');$schoolId=current_school_id();$uid=auth_user()['id'];
$sq=db()->prepare('SELECT class_id FROM students WHERE school_id=? AND user_id=?');$sq->execute([$schoolId,$uid]);$classId=(int)$sq->fetchColumn();
$tests=db()->prepare('SELECT * FROM test_updates WHERE school_id=? AND class_id=? ORDER BY test_date');$tests->execute([$schoolId,$classId]);
include __DIR__ . '/../views/layout/header.php'; include __DIR__ . '/../views/layout/sidebar.php'; include __DIR__ . '/../views/layout/topbar.php'; ?>
<div class="card p-3"><h5>Upcoming Tests</h5><table class="table"><tr><th>Title</th><th>Date</th></tr><?php foreach($tests as $t):?><tr><td><?=e($t['title'])?></td><td><?=$t['test_date']?></td></tr><?php endforeach;?></table></div>
<?php include __DIR__ . '/../views/layout/footer.php'; ?>
