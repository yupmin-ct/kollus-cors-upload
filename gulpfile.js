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

gulp.task('copy', function() {
    // Copy jQuery, Bootstrap, and FontAwesome
    gulp.src("bower_components/jquery/dist/jquery.js")
        .pipe(gulp.dest("resources/assets/js/"));

    gulp.src("bower_components/bootstrap/less/**")
        .pipe(gulp.dest("resources/assets/less/bootstrap"));

    gulp.src("bower_components/bootstrap/dist/js/bootstrap.js")
        .pipe(gulp.dest("resources/assets/js/"));

    gulp.src("bower_components/bootstrap/dist/fonts/**")
        .pipe(gulp.dest("public/assets/fonts"));

    gulp.src("bower_components/font-awesome/less/**")
        .pipe(gulp.dest("resources/assets/less/font-awesome"));

    gulp.src("bower_components/font-awesome/fonts/**")
        .pipe(gulp.dest("public/assets/fonts"));

    gulp.src("bower_components/ua-parser-js/src/ua-parser.js")
        .pipe(gulp.dest("resources/assets/js/"));
});

gulp.task('concat', function () {
    gulp.src([
            'resources/assets/js/jquery.js',
            'resources/assets/js/bootstrap.js',
            'resources/assets/js/ua-parser.js',
        ])
        .pipe(concat('default.js'))
        .pipe(header(banner, { pkg: pkg }))
        .pipe(gulp.dest('public/assets/js'));

    gulp.src(['resources/assets/less/default.less'])
        .pipe(less())
        .pipe(gulp.dest('public/assets/css'));
});

gulp.task('default', ['copy', 'concat']);
