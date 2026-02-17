<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../helpers/security.php';
require_login('admin');
verify_csrf();

$schoolId = current_school_id();
$grades = range(1, 13);
$letters = range('A', 'K');

function section_for_grade(int $grade): string
{
    if ($grade >= 1 && $grade <= 5) {
        return 'primary';
    }

    if ($grade >= 6 && $grade <= 11) {
        return 'secondary';
    }

    return 'al';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $grade = (int)($_POST['grade'] ?? 0);
        $letter = strtoupper(trim((string)($_POST['letter'] ?? '')));

        if ($grade < 1 || $grade > 13 || !in_array($letter, $letters, true)) {
            flash('danger', 'Invalid class grade or letter.');
        } else {
            $className = $grade . '-' . $letter;

            $exists = db()->prepare('SELECT id FROM classes WHERE school_id=? AND name=? LIMIT 1');
            $exists->execute([$schoolId, $className]);

            if ($exists->fetchColumn()) {
                flash('warning', 'Class already exists.');
            } else {
                $section = section_for_grade($grade);
                db()->prepare('INSERT INTO classes (school_id, name, year, section) VALUES (?, ?, YEAR(CURDATE()), ?)')
                    ->execute([$schoolId, $className, $section]);
                flash('success', 'Class added successfully.');
            }
        }
    }
}

$syncSections = db()->prepare(
    "UPDATE classes
     SET section = CASE
         WHEN CAST(SUBSTRING_INDEX(name, '-', 1) AS UNSIGNED) BETWEEN 1 AND 5 THEN 'primary'
         WHEN CAST(SUBSTRING_INDEX(name, '-', 1) AS UNSIGNED) BETWEEN 6 AND 11 THEN 'secondary'
         WHEN CAST(SUBSTRING_INDEX(name, '-', 1) AS UNSIGNED) BETWEEN 12 AND 13 THEN 'al'
         ELSE section
     END
     WHERE school_id = ?"
);
$syncSections->execute([$schoolId]);

$classes = db()->prepare('SELECT id,name,year,section FROM classes WHERE school_id=? ORDER BY CAST(SUBSTRING_INDEX(name, '-', 1) AS UNSIGNED), SUBSTRING_INDEX(name, '-', -1)');
$classes->execute([$schoolId]);
$classes = $classes->fetchAll();

include __DIR__ . '/../views/layout/header.php';
include __DIR__ . '/../views/layout/sidebar.php';
include __DIR__ . '/../views/layout/topbar.php';
?>
<div class="card p-3 mb-3">
    <h5>Add Class</h5>
    <form method="post" class="row g-2">
        <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="action" value="create">

        <div class="col-md-3">
            <select name="grade" class="form-select" required>
                <option value="">Select Grade</option>
                <?php foreach ($grades as $grade): ?>
                    <option value="<?= $grade ?>"><?= $grade ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-md-3">
            <select name="letter" class="form-select" required>
                <option value="">Select Letter</option>
                <?php foreach ($letters as $letter): ?>
                    <option value="<?= $letter ?>"><?= $letter ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-md-2">
            <button class="btn btn-primary">Add Class</button>
        </div>
    </form>
</div>

<div class="card p-3">
    <h5 class="mb-3">Classes</h5>
    <table class="table">
        <thead>
            <tr>
                <th>Class</th>
                <th>Year</th>
                <th>Section</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($classes as $class): ?>
                <tr>
                    <td><?= e($class['name']) ?></td>
                    <td><?= e((string)$class['year']) ?></td>
                    <td><?= e($class['section']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php include __DIR__ . '/../views/layout/footer.php'; ?>
