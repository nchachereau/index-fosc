- `git clone <thisrepo>`
- `git checkout production`
- `composer install --optimize-autoloader --no-dev`
- `php artisan config:cache`
- `php artisan view:cache`
- create `.env` based on `env.production`:
  ```
  cp env.production .env
  php artisan key:gen
  ```
- adapt `public/.htaccess` as needed (e.g. change `RewriteBase`)
- link `public/` in the webroot
