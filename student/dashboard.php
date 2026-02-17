<?php
require_once __DIR__ . '/../config/db.php'; require_once __DIR__ . '/../middleware/auth.php';
require_login('student');$schoolId=current_school_id();$uid=auth_user()['id'];
$sq=db()->prepare('SELECT * FROM students WHERE school_id=? AND user_id=?');$sq->execute([$schoolId,$uid]);$student=$sq->fetch();
$updates=db()->prepare("SELECT * FROM updates WHERE school_id=? AND audience IN ('all','students') ORDER BY publish_at DESC LIMIT 10");$updates->execute([$schoolId]);
$day=date('D');
$abs=db()->prepare("SELECT DISTINCT t.name FROM absences a JOIN timetable_entries te ON te.teacher_id=a.teacher_id AND te.school_id=a.school_id JOIN teachers t ON t.id=a.teacher_id AND t.school_id=a.school_id WHERE a.school_id=? AND a.date=CURDATE() AND te.class_id=? AND te.day_of_week=?");
$abs->execute([$schoolId,$student['class_id'],$day]);
include __DIR__ . '/../views/layout/header.php'; include __DIR__ . '/../views/layout/sidebar.php'; include __DIR__ . '/../views/layout/topbar.php'; ?>
<div class="row g-3"><div class="col-md-8"><div class="card p-3"><h5>Updates</h5><?php foreach($updates as $u):?><div class="mb-2"><b><?=e($u['title'])?></b><div><?=e($u['body'])?></div></div><?php endforeach;?></div></div>
<div class="col-md-4"><div class="card p-3"><h5>Absent Notices</h5><?php foreach($abs as $a):?><div class="alert alert-warning p-2">Teacher <?=e($a['name'])?> is absent today.</div><?php endforeach;?></div></div></div>
<?php include __DIR__ . '/../views/layout/footer.php'; ?>
