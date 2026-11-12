# Moviezzz: database and PHP starter

Prepared for Team N from your uploaded `Presentation.pptx` and the professor's HR sample. This is a proposed revision, not a claim about the professor's exact feedback. The updated presentation's scope is movies, reviews, favorites and forums. TV episodes and recommendation engines are not added here.

## Start here on your Mac

1. Extract this ZIP. Keep the folder name `moviezzz`.
2. Start Apache and MySQL in XAMPP Manager, following your professor's instructions. Open `http://localhost/phpmyadmin/` (or use the phpMyAdmin link in the XAMPP dashboard).
3. Click **Databases**. Create **moviezzz** with collation **utf8mb4_unicode_ci**. Select it in the left sidebar. Keep the professor's HR_Data database separate.
4. Open **Import**, choose `sql/01_schema.sql`, and run it. Alternatively paste that file into the SQL tab, just as in your professor's sample. Run this only on a new, empty database.
5. Import `sql/02_seed.sql` next. Expected result: **18 tables + 1 view**, six fictional movies, and no user accounts yet. Sample data is intentionally fictional so it does not pretend to be verified movie metadata.
6. Place the entire `moviezzz` folder in your XAMPP `htdocs` directory. A common Mac location is `/Applications/XAMPP/xamppfiles/htdocs/`; confirm the location for your installation.
7. Copy `app/config.example.php` to `app/config.local.php`. Set your MySQL host, port, database name, username and password. The example's root/blank-password values are placeholders for a local XAMPP installation, not credentials verified on your Mac.
8. Open **http://localhost/moviezzz/public/**. If your Apache uses another port, include it in the URL. You must use the URL, not double-click a PHP file.
9. Register an account, open a movie, submit a rating, edit it, and favorite the movie. Return to Movies and enter **90** in the time filter. The fictional fixtures should return **Weekend on Mars** and **The Last Bus Home**.
10. In phpMyAdmin, inspect `contacts`, `users`, `credentials`, `reviews`, and `favorites` to see what your actions saved.

Requirements: PHP 8.0+ with PDO MySQL; MariaDB 10.2.1+ or MySQL 8.0.16+ with InnoDB and CHECK constraints enabled. In phpMyAdmin run `SELECT VERSION();` to see the actual database engine/version. XAMPP may label its server “MySQL” while shipping MariaDB.

If you already have a populated Moviezzz database, stop before importing this schema. This package creates a new schema; it is not a migration script. Do not erase your existing database to make an import error disappear.

## What is included

| File or folder | Purpose |
|---|---|
| `sql/01_schema.sql` | Creates all tables, keys, checks, indexes and the rating view |
| `sql/02_seed.sql` | Creates fictional sample catalog records and the member/admin roles |
| `sql/03_practice_queries.sql` | Read-only queries you can run in phpMyAdmin |
| `app/config.example.php` | Database settings template |
| `app/bootstrap.php` | PDO connection, sessions, prepared queries, output escaping and form tokens |
| `public/index.php` | Search, genre, runtime and minimum-rating filters, plus favorites view |
| `public/movie.php` | Movie details, review create/update/delete, favorites and review links |
| `public/register.php`, `login.php`, `logout.php` | Working authentication code to try locally |
| `docs/ERD_REVIEW.md` | Explanation of every proposed ERD change and implementation choices |
| `docs/01_accounts`, `02_catalog`, `03_reviews`, `04_forums` | Revised ERDs as PNG, SVG and editable Graphviz DOT source |
| `docs/05_data_dictionary.md` | Column definitions |
| `tests/check_database.php` | Transactional checks to run with PHP after local setup |

## What PHP and MySQL each do

MySQL/MariaDB stores your data and enforces table relationships. SQL creates the database structure and reads/writes rows. phpMyAdmin gives you a browser interface to execute SQL. PHP receives the website's requests and connects to the database through PDO. Your Bootstrap interface can use these PHP pages or reuse their queries and handlers later.

For example, submitting a review causes `movie.php` to validate the form, take the user ID from the signed-in session and insert or update a `reviews` row. The rating view recalculates the displayed average from visible reviews. The page redirects after saving so refresh does not repeat the form submission.

## First work session

Get the database imported and complete one full flow: **register → login → open movie → rate → edit → favorite**. Check the saved rows in phpMyAdmin. Understand the three key files first: `01_schema.sql`, `bootstrap.php`, then `movie.php`. Run the practice queries to learn the joins.

Next, connect your team's Bootstrap layouts to this flow. After that, divide work among admin/movie editing, account settings/password reset, and forums. The current database supports these later features, but their PHP screens are not implemented here. Do not describe this starter as the completed project.

## Implemented versus remaining

| Area | Current package |
|---|---|
| Accounts and login/logout | PHP included; member role assigned on registration |
| Catalog, details, search, runtime filter | PHP included; plain local CSS, ready to adapt to Bootstrap |
| Review ratings/text and editing/deletion | PHP included; one review per user/movie |
| Favorites and review links | PHP included; copy the review link to share |
| Role-based admin actions | Role data available; admin pages and role guards still to build |
| Forums, replies, thread votes | Tables included; PHP screens still to build |
| Edit account/password | Tables support it; PHP screens still to build |
| Forgot password | Token table included; token generation, email delivery and reset handler still to build |
| Moderation and user removal | State fields included; admin workflows still to build |

This is a local class-project starter. Before public deployment, add login throttling, HTTPS configuration, password-reset delivery and expiry handling, admin authorization on every admin request, pagination, and your team's end-to-end tests. Keep the document root at `public/` in a hosted setup. The supplied Apache rules deny access to `app/` and `sql/` when overrides are enabled; never rely solely on those rules to protect a public deployment.

## Validation status

The SQL files were parsed in MySQL dialect. All eight PHP files, including the database test script, passed a PHP syntax-tree parser. Revised diagram images were inspected. No PHP executable or MySQL/MariaDB server was available in the preparation environment, so actual database imports and HTTP flows have **not** been run here. Run the checks below on your XAMPP installation before presenting the project.

After configuring `app/config.local.php`, run from Terminal in the `moviezzz` directory:

```bash
/Applications/XAMPP/xamppfiles/bin/php tests/check_database.php
```

Use your actual PHP path if it differs. Tests insert temporary records inside a transaction and roll them back; auto-increment counters can advance. They test constraints and average calculations. The browser flow still needs the manual checks in `tests/MANUAL_CHECKS.md`.

## Troubleshooting

- **Database connection failed:** confirm MySQL is running and compare the actual host/port/credentials with `config.local.php`. Try `127.0.0.1` to use TCP when `localhost` resolves to an unexpected socket.
- **Could not find driver:** enable/install PDO MySQL in the PHP instance serving the site. XAMPP PHP and a different Terminal PHP may have different extensions.
- **Table already exists:** schema import was already attempted or the selected database is not empty. Inspect the tables; do not repeatedly import or drop data blindly.
- **Table/view not found:** check the selected database and whether the schema import finished, including `movie_rating_stats`.
- **Registration fails:** verify `02_seed.sql` created the `member` role. A duplicate email/username is rejected deliberately.
- **No movies after filtering:** the initial sample has no ratings. Keep minimum rating on “Any rating” until users have reviewed movies.

The professor's README spells `HR_Data` as `HR_Date` in one step; select the database you actually created. His schema and data split is the pattern followed here.
