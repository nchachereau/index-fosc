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
- `npm install`, `npm run prod`
  si `npm` pas disponible sur l'hébergement:
  - générer en local
  - `scp -r` public/css, public/js et public/fonts
- link `public/` in the webroot
