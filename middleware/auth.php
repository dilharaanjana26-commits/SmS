<?php

require_once __DIR__ . '/../helpers/security.php';

function auth_user(): ?array
{
    ensure_session_started();
    return $_SESSION['user'] ?? null;
}

function require_login(?string $role = null): void
{
    $user = auth_user();
    if (!$user) {
        header('Location: /index.php?route=auth/admin_login');
        exit;
    }

    if ($role && $user['role'] !== $role) {
        http_response_code(403);
        exit('Forbidden: role mismatch');
    }
}

function require_any_role(array $roles): void
{
    $user = auth_user();
    if (!$user || !in_array($user['role'], $roles, true)) {
        http_response_code(403);
        exit('Forbidden');
    }
}

function current_school_id(): int
{
    $user = auth_user();
    return (int)($user['school_id'] ?? 0);
}

function flash(string $type, string $message): void
{
    ensure_session_started();
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function pull_flashes(): array
{
    ensure_session_started();
    $flashes = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $flashes;
}
