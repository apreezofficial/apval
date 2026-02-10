# Apval PHP Backend Export

This folder contains a PHP/MySQL implementation of the Apval backend, designed to replace the file-based database for hosting on cPanel.

## Setup Instructions

1. **Database Setup**:
   - Log in to your cPanel.
   - Go to **MySQL Database Wizard**.
   - Create a database named `apval_db` (or any name you prefer).
   - Create a user and assign them to the database with all privileges.
   - Open **phpMyAdmin**, select your new database, and import the `db_schema.sql` file provided here.

2. **Configure Connection**:
   - Open `api/db.php`.
   - Update `$db`, `$user`, and `$pass` with your cPanel database details.

3. **Upload**:
   - Upload the contents of the `api/` folder to your server (e.g., into `/public_html/apval/api/`).

4. **Update Frontend**:
   - In your Next.js project, open `src/lib/config.ts`.
   - Change the `apiUrl` to point to your new hosted API:
     ```typescript
     apiUrl: "https://preciousadedokun.com.ng/apval/api"
     ```

## API Endpoints Supported
- `POST /api/auth/login`
- `POST /api/auth/register`
- `GET /api/valentines/user/[id]`
- `GET /api/valentines/[id_or_slug]`
- `POST /api/valentines`
- `PUT /api/valentines`
- `DELETE /api/valentines?id=[id]`
- `GET /api/user?id=[id]`

### Note on Security
The passwords in this export are stored as plain text to match your current local implementation. For a production app, it is highly recommended to use `password_hash()` and `password_verify()` in `login.php` and `register.php`.
