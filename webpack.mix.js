const mix = require('laravel-mix');

mix
    .scripts(['resources/js/notification.js'], 'public/js/all.js')
    .sass('resources/sass/app.scss', 'public/css')
    .copyDirectory('resources/fonts', 'public/fonts')
    .options({
        processCssUrls: false
    });
