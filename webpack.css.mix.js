const mix = require('laravel-mix')
const tailwindcss = require('tailwindcss');
const path = require('path')
const mergeManifest = require('./mergeManifest')

mix.extend('mergeManifest', mergeManifest)
/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.options({
    processCssUrls: false,
    postCss: [ tailwindcss('tailwind.config.js') ],
})

mix.sass('resources/sass/app.scss', 'public/css')
  .mergeManifest();

if (mix.inProduction()) {
  mix.version()
} else {
  mix.sourceMaps()
}
