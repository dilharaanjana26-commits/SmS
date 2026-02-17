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
    } elseif ($action === 'deleteTeacher') {
        header('Content-Type: application/json');
        $teacherId = (int)($_POST['id'] ?? 0);

        if ($teacherId <= 0) {
            http_response_code(422);
            echo json_encode(['success' => false, 'message' => 'Invalid teacher id.']);
            exit;
        }

        try {
            $delete = db()->prepare('DELETE FROM teachers WHERE id = ? AND school_id = ?');
            $delete->execute([$teacherId, $schoolId]);

            if ($delete->rowCount() < 1) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Teacher not found.']);
                exit;
            }

            echo json_encode(['success' => true, 'message' => 'Teacher deleted successfully.', 'id' => $teacherId]);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Unable to delete teacher.']);
        }

        exit;
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
<?php foreach($teachers as $t): ?><tr id="teacher-row-<?=$t['id']?>"><td><?=e($t['name'])?></td><td><?=e($t['username'])?></td><td><?=e($t['subjects_text'])?></td><td><span class="badge bg-<?= $t['active']?'success':'secondary' ?>"><?= $t['active']?'active':'inactive' ?></span></td><td>
<a href="/index.php?route=admin/teachers&edit=<?=$t['id']?>" class="btn btn-sm btn-outline-secondary me-1">Edit</a><button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteTeacher(<?=$t['id']?>, this)">Delete</button>
</td></tr><?php endforeach; ?></tbody></table></div>
<div class="toast-container position-fixed bottom-0 end-0 p-3" id="toastContainer"></div>
<script>
function showToast(message, type = 'success') {
  const container = document.getElementById('toastContainer');
  const toast = document.createElement('div');
  toast.className = `toast align-items-center text-bg-${type === 'success' ? 'success' : 'danger'} border-0`;
  toast.setAttribute('role', 'alert');
  toast.setAttribute('aria-live', 'assertive');
  toast.setAttribute('aria-atomic', 'true');
  toast.innerHTML = `<div class="d-flex"><div class="toast-body">${message}</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button></div>`;
  container.appendChild(toast);
  const bsToast = new bootstrap.Toast(toast, { delay: 2500 });
  bsToast.show();
  toast.addEventListener('hidden.bs.toast', () => toast.remove());
}

async function deleteTeacher(id, button) {
  if (!confirm('Delete this teacher permanently?')) return;
  button.disabled = true;

  const payload = new URLSearchParams();
  payload.append('_csrf', '<?=e(csrf_token())?>');
  payload.append('action', 'deleteTeacher');
  payload.append('id', String(id));

  try {
    const response = await fetch('/index.php?route=admin/teachers', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: payload.toString()
    });

    const result = await response.json();
    if (!response.ok || !result.success) {
      throw new Error(result.message || 'Delete failed.');
    }

    const row = document.getElementById(`teacher-row-${id}`);
    if (row) row.remove();
    showToast(result.message || 'Teacher deleted successfully.', 'success');
  } catch (error) {
    button.disabled = false;
    showToast(error.message || 'Unable to delete teacher.', 'error');
  }
}
</script>
<?php include __DIR__ . '/../views/layout/footer.php'; ?>
