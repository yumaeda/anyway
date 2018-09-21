var gulp   = require('gulp'), 
    concat = require('gulp-concat'),
    sass   = require('gulp-sass');

gulp.task('default', [ 'sass' ], function(){});

gulp.task('sass', function()
{
    return gulp.src('./sass/**/*.scss')
        .pipe(concat('index.min.css'))
        .pipe(sass({ outputStyle: 'compressed' }))
        .pipe(gulp.dest('./target'));
});

