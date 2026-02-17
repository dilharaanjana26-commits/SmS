<?php
require_once __DIR__ . '/../config/db.php'; require_once __DIR__ . '/../middleware/auth.php'; require_once __DIR__ . '/../helpers/security.php';
require_login('teacher');verify_csrf();$schoolId=current_school_id();$uid=auth_user()['id'];
$q=db()->prepare('SELECT id FROM teachers WHERE school_id=? AND user_id=?');$q->execute([$schoolId,$uid]);$teacherId=(int)$q->fetchColumn();
if($_SERVER['REQUEST_METHOD']==='POST'){db()->prepare("INSERT INTO leave_requests (school_id,teacher_id,date_from,date_to,reason,status,created_at) VALUES (?,?,?,?,?,'pending',NOW())")->execute([$schoolId,$teacherId,$_POST['date_from'],$_POST['date_to'],clean_input($_POST['reason'])]);flash('success','Request submitted');}
$rows=db()->prepare('SELECT * FROM leave_requests WHERE school_id=? AND teacher_id=? ORDER BY created_at DESC');$rows->execute([$schoolId,$teacherId]);
include __DIR__ . '/../views/layout/header.php'; include __DIR__ . '/../views/layout/sidebar.php'; include __DIR__ . '/../views/layout/topbar.php'; ?>
<div class="card p-3 mb-3"><form method="post" class="row g-2"><input type="hidden" name="_csrf" value="<?=e(csrf_token())?>"><div class="col-md-3"><input type="date" name="date_from" class="form-control"></div><div class="col-md-3"><input type="date" name="date_to" class="form-control"></div><div class="col-md-4"><input name="reason" class="form-control" placeholder="Reason"></div><div class="col-md-2"><button class="btn btn-primary">Submit</button></div></form></div>
<div class="card p-3"><table class="table"><tr><th>From</th><th>To</th><th>Status</th></tr><?php foreach($rows as $r):?><tr><td><?=$r['date_from']?></td><td><?=$r['date_to']?></td><td><span class="badge bg-secondary"><?=$r['status']?></span></td></tr><?php endforeach;?></table></div>
<?php include __DIR__ . '/../views/layout/footer.php'; ?>
