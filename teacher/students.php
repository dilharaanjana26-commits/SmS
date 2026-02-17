<?php
require_once __DIR__ . '/../config/db.php'; require_once __DIR__ . '/../middleware/auth.php';
require_login('teacher');$schoolId=current_school_id();$uid=auth_user()['id'];
$q=db()->prepare('SELECT id FROM teachers WHERE school_id=? AND user_id=?');$q->execute([$schoolId,$uid]);$teacherId=(int)$q->fetchColumn();
$rows=db()->prepare("SELECT DISTINCT s.* FROM students s JOIN timetable_entries te ON te.class_id=s.class_id AND te.school_id=s.school_id WHERE s.school_id=? AND te.teacher_id=?");$rows->execute([$schoolId,$teacherId]);
include __DIR__ . '/../views/layout/header.php'; include __DIR__ . '/../views/layout/sidebar.php'; include __DIR__ . '/../views/layout/topbar.php'; ?>
<input class="form-control mb-2" placeholder="Search students" data-search-target="#stTbl"><div class="card p-3"><table class="table" id="stTbl"><tr><th>Name</th><th>Class</th><th>WhatsApp</th></tr><?php foreach($rows as $r):?><tr><td><?=e($r['name'])?></td><td><?=$r['class_id']?></td><td><?=e($r['whatsapp'])?></td></tr><?php endforeach;?></table></div>
<?php include __DIR__ . '/../views/layout/footer.php'; ?>
