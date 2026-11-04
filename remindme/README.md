  # RemindMe (Campus Bulletin Board)

PHP + MySQL (XAMPP) project.

## Setup
1. Copy this folder to `C:\xampp\htdocs\remindme`
2. Start Apache and MySQL in the XAMPP Control Panel
3. phpMyAdmin (http://localhost/phpmyadmin) -> Import -> `database.sql`
4. Open http://localhost/remindme/setup.php once, then delete `setup.php`
5. Open http://localhost/remindme/ and log in

## Test accounts
| Dashboard | Email | Password |
|---|---|---|
| Admin   | admin@remindme.test   | admin123   |
| Faculty | faculty@remindme.test | faculty123 |
| Student | student@remindme.test | student123 |

## Notes
- `migration.sql` is only for databases imported from an older version. A fresh import of `database.sql` does not need it.
- Database name: `remindme` (change it in `config.php` if needed).
