const mix = require('laravel-mix');
const path = require('path')

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel applications. By default, we are compiling the CSS
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.js('resources/js/app.js', 'public/js')
    .vue()
    .options( {
        processCssUrls: false,
    })
    .alias({
        '@': path.join(__dirname, 'resources/js')
    })
    .webpackConfig({
      output: { chunkFilename: 'js/[name].js?id=[chunkhash]' }
    })
    .babelConfig({
      plugins: ['@babel/plugin-syntax-dynamic-import'],
    });
    //.extract()

mix.postCss('resources/sass/app.css', 'public/css', [
    require("tailwindcss")
]);

if (mix.inProduction()) {
  mix.version();
} else {
  mix.sourceMaps();
}
