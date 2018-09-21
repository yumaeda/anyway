var gulp      = require('gulp'), 
    concat    = require('gulp-concat');

gulp.task('default', [ 'sass', 'lint-css', 'riot', 'eslint', 'js', 'php', 'image', 'page', 'plugin' ], function()
{
});

gulp.task('sass', function()
{
    const sass = require('gulp-sass');

    return gulp.src('./src/sass/**/*.scss')
        .pipe(concat('index.min.css'))
        .pipe(sass({ outputStyle: 'expanded' }))
        .pipe(gulp.dest('/var/www/html/anyway-grapes'));
});

gulp.task('lint-css', function lintCssTask()
{
    const gulpStylelint = require('gulp-stylelint');

    return gulp
    .src('/var/www/html/anyway-grapes/**/*.css')
    .pipe(gulpStylelint({
        reporters: [
            { formatter: 'string', console: true }
        ]
    }));
});

gulp.task('riot', function()
{
    return gulp.src('./src/riot/**/*.tag')
        .pipe(concat('index.tag'))
        .pipe(gulp.dest('/var/www/html/anyway-grapes'));
});

gulp.task('eslint', () => {
    const eslint = require('gulp-eslint');

    return gulp.src(['./js/**/*.js', '!node_modules/**'])
        .pipe(eslint())
        .pipe(eslint.format())
        .pipe(eslint.failAfterError());
});

gulp.task('js', function()
{
    return gulp.src('./src/js/**/*.js')
        .pipe(concat('index.min.js'))
        .pipe(gulp.dest('/var/www/html/anyway-grapes'));
});

gulp.task('php', function()
{
    return gulp.src('./*.php')
        .pipe(gulp.dest('/var/www/html/anyway-grapes'));
});

gulp.task('image', function()
{
    return gulp.src('./images/**/*.*')
        .pipe(gulp.dest('/var/www/html/anyway-grapes/images'));
});

gulp.task('page', function()
{
    return gulp.src('./pages/**/*.*')
        .pipe(gulp.dest('/var/www/html/anyway-grapes/pages'));
});

gulp.task('plugin', function()
{
    return gulp.src('./plugins/**/*.*')
        .pipe(gulp.dest('/var/www/html/anyway-grapes/plugins'));
});
