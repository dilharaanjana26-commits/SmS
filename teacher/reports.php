<?php
require_once __DIR__ . '/../config/db.php'; require_once __DIR__ . '/../middleware/auth.php'; require_once __DIR__ . '/../helpers/security.php';
require_login('teacher');verify_csrf();$schoolId=current_school_id();$uid=auth_user()['id'];
$q=db()->prepare('SELECT id FROM teachers WHERE school_id=? AND user_id=?');$q->execute([$schoolId,$uid]);$teacherId=(int)$q->fetchColumn();
if($_SERVER['REQUEST_METHOD']==='POST'){
 db()->prepare('INSERT INTO marks (school_id,class_id,student_id,subject,test_name,mark,updated_by,updated_at) VALUES (?,?,?,?,?,?,?,NOW()) ON DUPLICATE KEY UPDATE mark=VALUES(mark),updated_by=VALUES(updated_by),updated_at=NOW()')
 ->execute([$schoolId,(int)$_POST['class_id'],(int)$_POST['student_id'],clean_input($_POST['subject']),clean_input($_POST['test_name']),(float)$_POST['mark'],$teacherId]);
 flash('success','Mark saved');
}
$students=db()->prepare('SELECT id,name,class_id FROM students WHERE school_id=? AND active=1');$students->execute([$schoolId]);
include __DIR__ . '/../views/layout/header.php'; include __DIR__ . '/../views/layout/sidebar.php'; include __DIR__ . '/../views/layout/topbar.php'; ?>
<div class="card p-3"><form method="post" class="row g-2"><input type="hidden" name="_csrf" value="<?=e(csrf_token())?>"><div class="col-md-2"><input name="class_id" class="form-control" placeholder="Class ID"></div><div class="col-md-3"><select name="student_id" class="form-select"><?php foreach($students as $s):?><option value="<?=$s['id']?>"><?=e($s['name'])?></option><?php endforeach;?></select></div><div class="col-md-2"><input name="subject" class="form-control" placeholder="Subject"></div><div class="col-md-2"><input name="test_name" class="form-control" placeholder="Test"></div><div class="col-md-1"><input name="mark" class="form-control" placeholder="Mark"></div><div class="col-md-2"><button class="btn btn-primary">Save</button></div></form></div>
<?php include __DIR__ . '/../views/layout/footer.php'; ?>
