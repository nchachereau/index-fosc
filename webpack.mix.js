const mix = require('laravel-mix');

mix
    .sass('resources/sass/app.scss', 'public/css')
    .copyDirectory('resources/fonts', 'public/fonts')
    .options({
        processCssUrls: false
    });
