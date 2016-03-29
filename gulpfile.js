var fs = require('fs'),
  path = require('path'),
  gulp = require('gulp'),
  concat = require('gulp-concat'),
  header = require('gulp-header'),
  less = require('gulp-less'),
  pkg = JSON.parse(
    fs.readFileSync(path.resolve(__dirname, './package.json'))
  ),
  banner = ['/**',
      ' * Copyright (c) <%= new Date().getFullYear() %>',
      ' * <%= pkg.name %> - <%= pkg.description %>',
      ' * Built on <%= (new Date).toISOString().slice(0,10) %>',
      ' * ',
      ' * @version <%= pkg.version %>',
      ' * @link <%= pkg.repository.url %>',
      ' * @license <%= pkg.license %>',
      ' */',
      ''].join('\n') + '\n';

gulp.task('concat', function () {
  gulp.src([
      'src/cors-upload.js'
    ])
    .pipe(header(banner, {pkg: pkg}))
    .pipe(gulp.dest("public/assets/js/"));

  gulp.src(['resources/assets/less/default.less'])
    .pipe(less())
    .pipe(gulp.dest('public/assets/css'));
});

gulp.task('default', ['concat']);
