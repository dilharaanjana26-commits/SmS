<?php
require_once __DIR__ . '/../config/db.php'; require_once __DIR__ . '/../middleware/auth.php';
require_login('student');$schoolId=current_school_id();$uid=auth_user()['id'];
$sq=db()->prepare('SELECT id FROM students WHERE school_id=? AND user_id=?');$sq->execute([$schoolId,$uid]);$studentId=(int)$sq->fetchColumn();
$rows=db()->prepare('SELECT * FROM marks WHERE school_id=? AND student_id=? ORDER BY updated_at DESC');$rows->execute([$schoolId,$studentId]);
include __DIR__ . '/../views/layout/header.php'; include __DIR__ . '/../views/layout/sidebar.php'; include __DIR__ . '/../views/layout/topbar.php'; ?>
<div class="card p-3"><h5>Marks</h5><table class="table"><tr><th>Subject</th><th>Test</th><th>Mark</th></tr><?php foreach($rows as $r):?><tr><td><?=e($r['subject'])?></td><td><?=e($r['test_name'])?></td><td><?=$r['mark']?></td></tr><?php endforeach;?></table></div>
<?php include __DIR__ . '/../views/layout/footer.php'; ?>
