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
        $username = generate_username('s', $schoolId);
        $plain = generate_password();
        $hash = password_hash($plain, PASSWORD_BCRYPT);
        db()->beginTransaction();
        db()->prepare("INSERT INTO users (school_id, role, username, password_hash, language, status, created_at) VALUES (?, 'student', ?, ?, 'en', 'active', NOW())")->execute([$schoolId, $username, $hash]);
        $userId = (int)db()->lastInsertId();
        db()->prepare('INSERT INTO students (school_id,user_id,name,address,whatsapp,class_id,age_group,admitted_at,active) VALUES (?,?,?,?,?,?,?,CURDATE(),1)')->execute([$schoolId,$userId,clean_input($_POST['name']),clean_input($_POST['address']),clean_input($_POST['whatsapp']),(int)$_POST['class_id'],clean_input($_POST['age_group'])]);
        db()->commit();
        $generated = ['username'=>$username,'password'=>$plain];
        flash('success','Student created. Save credentials now.');
    } elseif ($action === 'update') {
        db()->prepare('UPDATE students SET name=?,address=?,whatsapp=?,class_id=?,age_group=? WHERE id=? AND school_id=?')->execute([clean_input($_POST['name']),clean_input($_POST['address']),clean_input($_POST['whatsapp']),(int)$_POST['class_id'],clean_input($_POST['age_group']),(int)$_POST['id'],$schoolId]);
        flash('success','Student updated');
    } elseif ($action === 'delete') {
        $studentId = (int)($_POST['id'] ?? 0);
        db()->beginTransaction();
        try {
            $student = db()->prepare('SELECT user_id FROM students WHERE id=? AND school_id=?');
            $student->execute([$studentId, $schoolId]);
            $userId = (int)$student->fetchColumn();

            if ($userId < 1) {
                db()->rollBack();
                flash('warning', 'Student not found');
            } else {
                db()->prepare('DELETE FROM marks WHERE school_id=? AND student_id=?')->execute([$schoolId, $studentId]);
                db()->prepare('DELETE FROM students WHERE id=? AND school_id=?')->execute([$studentId, $schoolId]);
                db()->prepare("DELETE FROM users WHERE id=? AND school_id=? AND role='student'")->execute([$userId, $schoolId]);
                db()->commit();
                flash('success', 'Student deleted permanently');
            }
        } catch (Throwable $e) {
            if (db()->inTransaction()) {
                db()->rollBack();
            }
            flash('danger', 'Unable to delete student permanently');
        }
    }
}
$classes = db()->prepare('SELECT id,name FROM classes WHERE school_id=? ORDER BY name'); $classes->execute([$schoolId]); $classes=$classes->fetchAll();
if ($editId) {
    $q = db()->prepare('SELECT * FROM students WHERE id=? AND school_id=?');
    $q->execute([$editId,$schoolId]);
    $editing = $q->fetch();
}
$students = db()->prepare('SELECT s.*,u.username,c.name class_name FROM students s JOIN users u ON u.id=s.user_id LEFT JOIN classes c ON c.id=s.class_id AND c.school_id=s.school_id WHERE s.school_id=? ORDER BY s.id DESC');
$students->execute([$schoolId]);
include __DIR__ . '/../views/layout/header.php'; include __DIR__ . '/../views/layout/sidebar.php'; include __DIR__ . '/../views/layout/topbar.php';
?>
<div class="card p-3 mb-3"><h5><?= $editing ? 'Edit Student' : 'Add Student' ?></h5>
<form method="post" class="row g-2"><input type="hidden" name="_csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="<?= $editing ? 'update' : 'create' ?>"><input type="hidden" name="id" value="<?= (int)($editing['id'] ?? 0) ?>">
<div class="col-md-3"><input name="name" value="<?= e($editing['name'] ?? '') ?>" class="form-control" placeholder="Name" required></div>
<div class="col-md-2"><input name="address" value="<?= e($editing['address'] ?? '') ?>" class="form-control" placeholder="Address"></div>
<div class="col-md-2"><input name="whatsapp" value="<?= e($editing['whatsapp'] ?? '') ?>" class="form-control" placeholder="WhatsApp"></div>
<div class="col-md-2"><select name="class_id" class="form-select"><?php foreach($classes as $c): ?><option value="<?=$c['id']?>" <?= (($editing['class_id'] ?? 0)==$c['id'])?'selected':"" ?>><?=e($c['name'])?></option><?php endforeach; ?></select></div>
<div class="col-md-2"><select name="age_group" class="form-select"><option value="primary" <?= (($editing['age_group'] ?? "")=='primary')?'selected':"" ?>>primary</option><option value="secondary" <?= (($editing['age_group'] ?? "secondary")=='secondary')?'selected':"" ?>>secondary</option><option value="al" <?= (($editing['age_group'] ?? "")=='al')?'selected':"" ?>>al</option></select></div>
<div class="col-md-1"><button class="btn btn-primary">Create</button></div></form>
<?php if($generated): ?><div class="alert alert-info mt-3">Username: <b><?=e($generated['username'])?></b> | Password: <b><?=e($generated['password'])?></b> (shown once)</div><?php endif; ?>
</div>
<div class="card p-3"><table class="table"><thead><tr><th>Name</th><th>User</th><th>Class</th><th>Status</th><th></th></tr></thead><tbody>
<?php foreach($students as $s): ?><tr><td><?=e($s['name'])?></td><td><?=e($s['username'])?></td><td><?=e($s['class_name'] ?? '-')?></td><td><span class="badge bg-<?= $s['active']?'success':'secondary' ?>"><?= $s['active']?'active':'inactive' ?></span></td><td>
<a href="/index.php?route=admin/students&edit=<?=$s['id']?>" class="btn btn-sm btn-outline-secondary me-1">Edit</a><form method="post" class="d-inline"><input type="hidden" name="_csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?=$s['id']?>"><button class="btn btn-sm btn-outline-danger">Delete</button></form>
</td></tr><?php endforeach; ?></tbody></table></div>
<?php include __DIR__ . '/../views/layout/footer.php'; ?>
