# php-mail-logger
Contact form for websites that use PHP and MySQL. Submissions are saved to MySQL, emailed to a configurable recipient, and viewable in an admin area.

## Requirements
PHP 7.4+ (8.x recommended) with `mysqli` (mysqlnd) and MySQL/MariaDB. On older PHP the pages fail with a blank HTTP 500 because the code uses newer syntax.

## Deploying
Two parts: the **public files** (everything in `public/`) and the private **contact-src/** folder (code, schema.sql, your settings.local.php). The public files find contact-src automatically in any of these places, relative to the folder holding submit.php:

1. inside the public folder (e.g. `public_html/contact-form/contact-src/`)
2. next to it (e.g. `public_html/contact-src/`)
3. two levels up (e.g. `home/contact-src/` for `home/public_html/contact-form/`)

Outside the web root (option 3) is safest. contact-src ships with an `.htaccess` that denies web access (Apache/LiteSpeed) for when it sits under the web root; verify that `https://yoursite.com/.../contact-src/settings.local.php` does NOT load. To force a location, define `CONTACT_SRC` at the top of locate.php.

Example for `https://example.com/contact-form/`:
```
public_html/
├── contact-form/    <- contents of public/ (form.js, submit.php, locate.php, setup.php, admin/ ...)
└── contact-src/     <- or inside contact-form/, or above public_html/
```

## Setup
1. Create an empty MySQL database and user.
2. `cp contact-src/settings.sample.php contact-src/settings.local.php` and fill in DB credentials.
3. Upload the public files to any folder or subfolder of your site (all links are relative).
4. Visit `/setup.php`: it creates the tables (from `contact-src/schema.sql`) and your admin account. **Then delete `setup.php`** — once an admin exists, the whole app returns 503 until the file is gone. (CLI alternative: `php bin/create-admin.php`; SQL alternative: [create-admin.sql](create-admin.sql).)
5. Log in at `/admin/login.php`, set the recipient and confirmation message under **Settings**.

## Embed
```html
<script src="https://yoursite.com/form.js"></script>
```
Optionally place `<div id="contact-form"></div>` where the form should render. If the script is hosted on a different origin than the page, add the page's origin to `allowed_origins` in `contact-src/settings.local.php`. Test locally with `php -S localhost:8000 -t public` and open `/demo.html`.

## Notes
- Anti-spam: honeypot field and 5 submissions / 10 min per IP.
- Admin forms are CSRF-protected; all output is escaped; queries are prepared.
