<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/helpers/security.php';
require_once __DIR__ . '/helpers/i18n.php';

$route = $_GET['route'] ?? '';
$routes = [
    '' => 'auth/admin_login.php',
    'auth/admin_login' => 'auth/admin_login.php',
    'auth/teacher_login' => 'auth/teacher_login.php',
    'auth/student_login' => 'auth/student_login.php',
    'auth/logout' => 'auth/logout.php',

    'admin/dashboard' => 'admin/dashboard.php',
    'admin/teachers' => 'admin/teachers.php',
    'admin/students' => 'admin/students.php',
    'admin/classes' => 'admin/classes.php',
    'admin/timetables' => 'admin/timetables.php',
    'admin/absent' => 'admin/absent.php',
    'admin/relief' => 'admin/relief.php',
    'admin/updates' => 'admin/updates.php',

    'teacher/dashboard' => 'teacher/dashboard.php',
    'teacher/test_updates' => 'teacher/test_updates.php',
    'teacher/students' => 'teacher/students.php',
    'teacher/reports' => 'teacher/reports.php',
    'teacher/leave_requests' => 'teacher/leave_requests.php',
    'teacher/assignments' => 'teacher/assignments.php',

    'student/dashboard' => 'student/dashboard.php',
    'student/upcoming' => 'student/upcoming.php',
    'student/timetable' => 'student/timetable.php',
    'student/marks' => 'student/marks.php',
    'student/assignments' => 'student/assignments.php',
];

$file = $routes[$route] ?? null;
if (!$file || !file_exists(__DIR__ . '/' . $file)) {
    http_response_code(404);
    exit('Route not found');
}
require __DIR__ . '/' . $file;
