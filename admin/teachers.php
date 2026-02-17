<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../helpers/security.php';
require_login('admin');
verify_csrf();
$schoolId = current_school_id();
$generated = null;
$editId = (int)($_GET['edit'] ?? 0);
$editing = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'create') {
        $username = generate_username('t', $schoolId);
        $plain = generate_password();
        $hash = password_hash($plain, PASSWORD_BCRYPT);
        db()->beginTransaction();
        $u = db()->prepare("INSERT INTO users (school_id, role, username, password_hash, language, status, created_at) VALUES (?, 'teacher', ?, ?, 'en', 'active', NOW())");
        $u->execute([$schoolId, $username, $hash]);
        $userId = (int)db()->lastInsertId();
        $t = db()->prepare('INSERT INTO teachers (school_id,user_id,name,whatsapp,nic,degree_details,subjects_text,age_group,joined_at,active) VALUES (?,?,?,?,?,?,?,?,CURDATE(),1)');
        $t->execute([$schoolId,$userId,clean_input($_POST['name']),clean_input($_POST['whatsapp']),clean_input($_POST['nic']),clean_input($_POST['degree_details']),clean_input($_POST['subjects_text']),clean_input($_POST['age_group'])]);
        db()->commit();
        $generated = ['username'=>$username,'password'=>$plain];
        flash('success','Teacher created. Save credentials now.');
    } elseif ($action === 'update') {
        db()->prepare('UPDATE teachers SET name=?,whatsapp=?,nic=?,degree_details=?,subjects_text=?,age_group=? WHERE id=? AND school_id=?')->execute([clean_input($_POST['name']),clean_input($_POST['whatsapp']),clean_input($_POST['nic']),clean_input($_POST['degree_details']),clean_input($_POST['subjects_text']),clean_input($_POST['age_group']),(int)$_POST['id'],$schoolId]);
        flash('success','Teacher updated');
    } elseif ($action === 'delete') {
        db()->prepare('UPDATE teachers SET active=0 WHERE id=? AND school_id=?')->execute([(int)$_POST['id'],$schoolId]);
        flash('warning','Teacher deactivated');
    }
}
if ($editId) {
    $q = db()->prepare('SELECT * FROM teachers WHERE id=? AND school_id=?');
    $q->execute([$editId,$schoolId]);
    $editing = $q->fetch();
}
$teachers = db()->prepare('SELECT t.*,u.username,u.status FROM teachers t JOIN users u ON u.id=t.user_id WHERE t.school_id=? ORDER BY t.id DESC');
$teachers->execute([$schoolId]);
include __DIR__ . '/../views/layout/header.php'; include __DIR__ . '/../views/layout/sidebar.php'; include __DIR__ . '/../views/layout/topbar.php';
?>
<div class="card p-3 mb-3"><h5><?= $editing ? 'Edit Teacher' : 'Add Teacher' ?></h5>
<form method="post" class="row g-2"><input type="hidden" name="_csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="<?= $editing ? 'update' : 'create' ?>"><input type="hidden" name="id" value="<?= (int)($editing['id'] ?? 0) ?>">
<div class="col-md-3"><input name="name" value="<?= e($editing['name'] ?? '') ?>" class="form-control" placeholder="Name" required></div>
<div class="col-md-2"><input name="whatsapp" value="<?= e($editing['whatsapp'] ?? '') ?>" class="form-control" placeholder="WhatsApp"></div>
<div class="col-md-2"><input name="nic" value="<?= e($editing['nic'] ?? '') ?>" class="form-control" placeholder="NIC"></div>
<div class="col-md-2"><input name="degree_details" value="<?= e($editing['degree_details'] ?? '') ?>" class="form-control" placeholder="Degree"></div>
<div class="col-md-2"><input name="subjects_text" value="<?= e($editing['subjects_text'] ?? '') ?>" class="form-control" placeholder="Subjects"></div>
<div class="col-md-1"><select name="age_group" class="form-select"><option value="primary" <?= (($editing['age_group'] ?? "")=='primary')?'selected':"" ?>>primary</option><option value="secondary" <?= (($editing['age_group'] ?? "secondary")=='secondary')?'selected':"" ?>>secondary</option><option value="al" <?= (($editing['age_group'] ?? "")=='al')?'selected':"" ?>>al</option></select></div>
<div class="col-12"><button class="btn btn-primary">Create</button></div></form>
<?php if($generated): ?><div class="alert alert-info mt-3">Username: <b><?=e($generated['username'])?></b> | Password: <b><?=e($generated['password'])?></b> (shown once)</div><?php endif; ?>
</div>
<input class="form-control mb-2" placeholder="Search" data-search-target="#teachersTbl">
<div class="card p-3"><table class="table" id="teachersTbl"><thead><tr><th>Name</th><th>User</th><th>Subjects</th><th>Status</th><th></th></tr></thead><tbody>
<?php foreach($teachers as $t): ?><tr><td><?=e($t['name'])?></td><td><?=e($t['username'])?></td><td><?=e($t['subjects_text'])?></td><td><span class="badge bg-<?= $t['active']?'success':'secondary' ?>"><?= $t['active']?'active':'inactive' ?></span></td><td>
<a href="/index.php?route=admin/teachers&edit=<?=$t['id']?>" class="btn btn-sm btn-outline-secondary me-1">Edit</a><form method="post" class="d-inline"><input type="hidden" name="_csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?=$t['id']?>"><button class="btn btn-sm btn-outline-danger">Delete</button></form>
</td></tr><?php endforeach; ?></tbody></table></div>
<?php include __DIR__ . '/../views/layout/footer.php'; ?>
