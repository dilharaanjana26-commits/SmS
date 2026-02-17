<?php $role = $user['role'] ?? ''; ?>
<div class="border-end bg-white shadow-sm" id="sidebar-wrapper">
  <div class="sidebar-heading p-3 fw-bold"><?= e(t('app_name')) ?></div>
  <div class="list-group list-group-flush">
    <a class="list-group-item list-group-item-action" href="/index.php?route=<?= $role ?>/dashboard"><?= e(t('dashboard')) ?></a>
    <?php if ($role === 'admin'): ?>
      <a class="list-group-item list-group-item-action" href="/index.php?route=admin/teachers"><?= e(t('teachers')) ?></a>
      <a class="list-group-item list-group-item-action" href="/index.php?route=admin/students"><?= e(t('students')) ?></a>
      <a class="list-group-item list-group-item-action" href="/index.php?route=admin/classes">Classes</a>
      <a class="list-group-item list-group-item-action" href="/index.php?route=admin/timetables"><?= e(t('timetables')) ?></a>
      <a class="list-group-item list-group-item-action" href="/index.php?route=admin/absent">Absent</a>
      <a class="list-group-item list-group-item-action" href="/index.php?route=admin/relief">Relief</a>
      <a class="list-group-item list-group-item-action" href="/index.php?route=admin/updates"><?= e(t('updates')) ?></a>
    <?php elseif ($role === 'teacher'): ?>
      <a class="list-group-item list-group-item-action" href="/index.php?route=teacher/test_updates">Test Updates</a>
      <a class="list-group-item list-group-item-action" href="/index.php?route=teacher/students">Students</a>
      <a class="list-group-item list-group-item-action" href="/index.php?route=teacher/reports">Reports</a>
      <a class="list-group-item list-group-item-action" href="/index.php?route=teacher/leave_requests">Leave Requests</a>
      <a class="list-group-item list-group-item-action" href="/index.php?route=teacher/assignments">Assignments</a>
    <?php else: ?>
      <a class="list-group-item list-group-item-action" href="/index.php?route=student/upcoming">Upcoming</a>
      <a class="list-group-item list-group-item-action" href="/index.php?route=student/timetable">My Timetable</a>
      <a class="list-group-item list-group-item-action" href="/index.php?route=student/marks">Marks</a>
      <a class="list-group-item list-group-item-action" href="/index.php?route=student/assignments">Assignments</a>
    <?php endif; ?>
  </div>
</div>
