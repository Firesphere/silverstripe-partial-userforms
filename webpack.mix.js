const mix = require('laravel-mix');

const themePath = 'client';
const jsPath = `${themePath}/src/js/`;
const destPath = `${themePath}/dist/`;

const SRC = {
  js: jsPath + 'main.js',
};

const DEST = {
  css: destPath,
  js: destPath
};

mix.setPublicPath(__dirname);

mix.options({
  processCssUrls: false,
});

mix.js(SRC.js, DEST.js);