/*
npx mix
npx mix watch
npx mix --production
*/

let mix = require('laravel-mix');

mix.js(
    'themes/mytheme/src/app.js',
    'themes/mytheme/javascript/app.min.js'
);

mix.sass(
    'themes/mytheme/src/app.scss',
    'themes/mytheme/css/app.min.css'
).options({
    processCssUrls: false,
});

mix.sass(
    'themes/mytheme/src/editor.scss',
    'themes/mytheme/css/editor.css'
).options({
    processCssUrls: false,
});
