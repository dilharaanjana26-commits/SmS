<?php
require_once __DIR__ . '/../config/db.php'; require_once __DIR__ . '/../middleware/auth.php';
require_login('student');$schoolId=current_school_id();$uid=auth_user()['id'];
$sq=db()->prepare('SELECT class_id FROM students WHERE school_id=? AND user_id=?');$sq->execute([$schoolId,$uid]);$classId=(int)$sq->fetchColumn();
$rows=db()->prepare("SELECT te.*, pt.start_time FROM timetable_entries te LEFT JOIN period_times pt ON pt.school_id=te.school_id AND pt.period_no=te.period_no AND pt.day_type='normal' WHERE te.school_id=? AND te.timetable_type='class' AND te.owner_id=? ORDER BY te.day_of_week,te.period_no");$rows->execute([$schoolId,$classId]);
include __DIR__ . '/../views/layout/header.php'; include __DIR__ . '/../views/layout/sidebar.php'; include __DIR__ . '/../views/layout/topbar.php'; ?>
<div class="card p-3"><div class="d-flex justify-content-between"><h5>My Timetable</h5><button onclick="window.print()" class="btn btn-outline-secondary btn-sm">Print</button></div><table class="table"><tr><th>Day</th><th>Period</th><th>Time</th><th>Subject</th></tr><?php foreach($rows as $r):?><tr><td><?=$r['day_of_week']?></td><td><?=$r['period_no']?></td><td><?=$r['start_time']?></td><td><?=e($r['subject'])?></td></tr><?php endforeach;?></table></div>
<?php include __DIR__ . '/../views/layout/footer.php'; ?>
