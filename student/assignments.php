<?php
require_once __DIR__ . '/../config/db.php'; require_once __DIR__ . '/../middleware/auth.php';
require_login('student');$schoolId=current_school_id();$uid=auth_user()['id'];
$sq=db()->prepare('SELECT class_id FROM students WHERE school_id=? AND user_id=?');$sq->execute([$schoolId,$uid]);$classId=(int)$sq->fetchColumn();
$rows=db()->prepare('SELECT * FROM assignments WHERE school_id=? AND class_id=? ORDER BY due_date');$rows->execute([$schoolId,$classId]);
include __DIR__ . '/../views/layout/header.php'; include __DIR__ . '/../views/layout/sidebar.php'; include __DIR__ . '/../views/layout/topbar.php'; ?>
<div class="card p-3"><h5>Assignments</h5><table class="table"><tr><th>Title</th><th>Due Date</th><th>Details</th></tr><?php foreach($rows as $r):?><tr><td><?=e($r['title'])?></td><td><?=$r['due_date']?></td><td><?=e($r['body'])?></td></tr><?php endforeach;?></table></div>
<?php include __DIR__ . '/../views/layout/footer.php'; ?>
