'use strict';

var gulp = require('gulp');
var $ = require('gulp-load-plugins')();
var gutil = require('gulp-util');
var rename = require("gulp-rename");

var cssPath = 'skeletons/themes/theme1/static/css';
var jsPath = 'skeletons/themes/theme1/static/js';
var sassPaths = [
    'bower_components/normalize.scss/sass',
    'bower_components/foundation-sites/scss',
    'bower_components/motion-ui/src'
];

gulp.task('js', function () {
    return gulp.src('assets/js/main.js')
        .pipe($.uglify())
        .pipe(gulp.dest(jsPath));
});

gulp.task('sass', function () {
    return gulp.src('assets/scss/main.scss')
        .pipe($.sass({
            includePaths: sassPaths,
            outputStyle: 'compressed'
        })
            .on('error', $.sass.logError))
        .pipe($.autoprefixer({
            browsers: ['last 2 versions', 'ie >= 9']
        }))
        .pipe(gulp.dest(cssPath));
});

gulp.task('default', ['sass', 'js', 'watch']);
gulp.task('watch', ['sass', 'js'], function () {
    gulp.watch(['assets/scss/**/*.scss', 'assets/js/**/*.js'], ['sass', 'js']);
});