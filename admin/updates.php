<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../helpers/security.php';
require_login('admin'); verify_csrf(); $schoolId=current_school_id();
if($_SERVER['REQUEST_METHOD']==='POST'){
 db()->prepare('INSERT INTO updates (school_id,audience,title,body,publish_at,created_by,created_at) VALUES (?,?,?,?,?,?,NOW())')
 ->execute([$schoolId,$_POST['audience'],clean_input($_POST['title']),clean_input($_POST['body']),$_POST['publish_at'],auth_user()['id']]);
 flash('success','Update posted');
}
$rows=db()->prepare('SELECT * FROM updates WHERE school_id=? ORDER BY publish_at DESC');$rows->execute([$schoolId]);
include __DIR__ . '/../views/layout/header.php'; include __DIR__ . '/../views/layout/sidebar.php'; include __DIR__ . '/../views/layout/topbar.php';
?>
<div class="card p-3 mb-3"><h5>Post Announcement</h5><form method="post" class="row g-2"><input type="hidden" name="_csrf" value="<?=e(csrf_token())?>">
<div class="col-md-2"><select name="audience" class="form-select"><option>all</option><option>teachers</option><option>students</option></select></div>
<div class="col-md-3"><input name="title" class="form-control" placeholder="Title"></div>
<div class="col-md-4"><input name="body" class="form-control" placeholder="Body"></div>
<div class="col-md-2"><input type="datetime-local" name="publish_at" class="form-control"></div>
<div class="col-md-1"><button class="btn btn-success">Post</button></div></form></div>
<div class="card p-3"><ul class="list-group"><?php foreach($rows as $r):?><li class="list-group-item"><span class="badge bg-info me-2"><?=e($r['audience'])?></span><b><?=e($r['title'])?></b><div><?=e($r['body'])?></div></li><?php endforeach;?></ul></div>
<?php include __DIR__ . '/../views/layout/footer.php'; ?>
