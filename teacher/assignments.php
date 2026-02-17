<?php
require_once __DIR__ . '/../config/db.php'; require_once __DIR__ . '/../middleware/auth.php'; require_once __DIR__ . '/../helpers/security.php';
require_login('teacher');verify_csrf();$schoolId=current_school_id();$uid=auth_user()['id'];
$q=db()->prepare('SELECT id FROM teachers WHERE school_id=? AND user_id=?');$q->execute([$schoolId,$uid]);$teacherId=(int)$q->fetchColumn();
if($_SERVER['REQUEST_METHOD']==='POST'){db()->prepare('INSERT INTO assignments (school_id,teacher_id,class_id,title,body,due_date,created_at) VALUES (?,?,?,?,?,?,NOW())')->execute([$schoolId,$teacherId,(int)$_POST['class_id'],clean_input($_POST['title']),clean_input($_POST['body']),$_POST['due_date']]);flash('success','Assignment posted');}
$classes=db()->prepare('SELECT id,name FROM classes WHERE school_id=?');$classes->execute([$schoolId]);
$rows=db()->prepare('SELECT a.*,c.name class_name FROM assignments a JOIN classes c ON c.id=a.class_id AND c.school_id=a.school_id WHERE a.school_id=? AND a.teacher_id=? ORDER BY a.created_at DESC');$rows->execute([$schoolId,$teacherId]);
include __DIR__ . '/../views/layout/header.php'; include __DIR__ . '/../views/layout/sidebar.php'; include __DIR__ . '/../views/layout/topbar.php'; ?>
<div class="card p-3 mb-3"><form method="post" class="row g-2"><input type="hidden" name="_csrf" value="<?=e(csrf_token())?>"><div class="col-md-2"><select name="class_id" class="form-select"><?php foreach($classes as $c):?><option value="<?=$c['id']?>"><?=e($c['name'])?></option><?php endforeach;?></select></div><div class="col-md-2"><input name="title" class="form-control" placeholder="Title"></div><div class="col-md-4"><input name="body" class="form-control" placeholder="Body"></div><div class="col-md-2"><input type="date" name="due_date" class="form-control"></div><div class="col-md-2"><button class="btn btn-primary">Post</button></div></form></div>
<div class="card p-3"><table class="table"><tr><th>Class</th><th>Title</th><th>Due</th></tr><?php foreach($rows as $r):?><tr><td><?=e($r['class_name'])?></td><td><?=e($r['title'])?></td><td><?=$r['due_date']?></td></tr><?php endforeach;?></table></div>
<?php include __DIR__ . '/../views/layout/footer.php'; ?>
