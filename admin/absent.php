<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../helpers/security.php';
require_login('admin');
verify_csrf();
$schoolId=current_school_id();
if($_SERVER['REQUEST_METHOD']==='POST'){
 db()->prepare('INSERT INTO absences (school_id,teacher_id,date,source,reason,created_by,created_at) VALUES (?,?,?,?,?,?,NOW())')
 ->execute([$schoolId,(int)$_POST['teacher_id'],$_POST['date'],$_POST['source'],clean_input($_POST['reason']),auth_user()['id']]);
 flash('success','Absence marked');
}
$teachers=db()->prepare('SELECT id,name FROM teachers WHERE school_id=? AND active=1');$teachers->execute([$schoolId]);
$rows=db()->prepare('SELECT a.*,t.name teacher FROM absences a JOIN teachers t ON t.id=a.teacher_id AND t.school_id=a.school_id WHERE a.school_id=? ORDER BY date DESC');$rows->execute([$schoolId]);
include __DIR__ . '/../views/layout/header.php'; include __DIR__ . '/../views/layout/sidebar.php'; include __DIR__ . '/../views/layout/topbar.php';
?>
<div class="card p-3 mb-3"><h5>Mark Teacher Absent</h5><form method="post" class="row g-2"><input type="hidden" name="_csrf" value="<?=e(csrf_token())?>">
<div class="col-md-3"><select name="teacher_id" class="form-select"><?php foreach($teachers as $t):?><option value="<?=$t['id']?>"><?=e($t['name'])?></option><?php endforeach;?></select></div>
<div class="col-md-2"><input type="date" name="date" class="form-control" value="<?=date('Y-m-d')?>"></div>
<div class="col-md-2"><select name="source" class="form-select"><option>online</option><option>physical</option></select></div>
<div class="col-md-3"><input name="reason" class="form-control" placeholder="Reason"></div>
<div class="col-md-2"><button class="btn btn-danger">Mark Absent</button></div></form></div>
<div class="card p-3"><table class="table"><thead><tr><th>Date</th><th>Teacher</th><th>Reason</th></tr></thead><tbody><?php foreach($rows as $r):?><tr><td><?=e($r['date'])?></td><td><?=e($r['teacher'])?></td><td><?=e($r['reason'])?></td></tr><?php endforeach;?></tbody></table></div>
<?php include __DIR__ . '/../views/layout/footer.php'; ?>
