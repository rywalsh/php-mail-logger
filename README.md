# php-mail-logger
Contact form for websites that use PHP and MySQL. Submissions are saved to MySQL, emailed to a configurable recipient, and viewable in an admin area.

## Setup
1. Create a MySQL database and run `schema.sql`.
2. `cp config.sample.php config.php` and fill in DB credentials (keep `config.php` outside/blocked from the web root if possible).
3. Point your web server's document root (or a subpath) at `public/`. The admin redirects assume it is served at `/admin/`.
4. Create an admin: `php bin/create-admin.php you@example.com 'a-strong-password'`
5. Log in at `/admin/login.php`, set the recipient and confirmation message under **Settings**.

## Embed
```html
<script src="https://yoursite.com/form.js"></script>
```
Optionally place `<div id="contact-form"></div>` where the form should render. If the script is hosted on a different origin than the page, add the page's origin to `allowed_origins` in `config.php`. Test locally with `php -S localhost:8000 -t public` and open `/demo.html`.

## Notes
- Anti-spam: honeypot field and 5 submissions / 10 min per IP.
- Admin forms are CSRF-protected; all output is escaped; queries are prepared.
