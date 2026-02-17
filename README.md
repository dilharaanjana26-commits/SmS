# School Management SaaS (PHP/MySQL/Bootstrap)

## Default Seed Login
- Admin URL: `/index.php?route=auth/admin_login`
- Username: `admin1`
- Password: `Admin@12345`

## Local Setup
1. Create database `sms_saas`.
2. Import `sql/schema.sql`.
3. Update `config/config.php` DB credentials.
4. Run with PHP server or Apache (`index.php` is root entry).

## InfinityFree Deployment
1. Zip and upload project to `htdocs/` so `/htdocs/index.php` exists.
2. In InfinityFree panel, create MySQL DB.
3. Import `sql/schema.sql` via phpMyAdmin.
4. Edit `/config/config.php` to InfinityFree DB host/name/user/pass.
5. Ensure PHP version supports `password_hash` (bcrypt, PHP 7.4+).
6. Open domain root and login using seeded admin.

## Security Notes
- Bcrypt hashes for all passwords.
- Session-based auth with role middleware.
- CSRF token required for all POST forms.
- Prepared PDO statements only.
- Multi-tenant filtering with `school_id` in queries.
