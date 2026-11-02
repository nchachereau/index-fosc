- `git clone <thisrepo>`
- `wget 'https://raw.githubusercontent.com/laravel/laravel/refs/heads/master/artisan'`
- `/opt/php8.5/bin/composer install --optimize-autoloader --no-dev` (adapt path to composer depending on PHP version)
- `/opt/php8.5/bin/php artisan config:cache` (adapt path to PHP)
- `/opt/php8.5/php artisan view:cache`
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
- link `public/` in the webroot (`cd web/; ln -s ../index-fosc/public/ index-fosc`)
