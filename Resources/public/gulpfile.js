function swallowError (error) {
    //If you want details of the error in the console
    console.log(error.toString());
    this.emit('end');
}

// Load plugins
var gulp         = require('gulp'),
    compass      = require('gulp-compass'),
    autoprefixer = require('gulp-autoprefixer'),
    csso         = require('gulp-csso'),
    bless        = require('gulp-bless'),
    rename       = require('gulp-rename'),
    concat       = require('gulp-concat'),
    uglify       = require('gulp-uglify'),
    livereload   = require('gulp-livereload'),
    notify       = require('gulp-notify'),
    del          = require('del');


// Styles
// -------------------------------------------------
gulp.task('styles', function() {
    return gulp.src('scss/main.scss')
        .pipe(compass({
            config_file: 'config.rb',
            css: 'css',
            sass: 'scss',
        }))
        .on('error', swallowError)
        .pipe(autoprefixer('last 2 version', 'ie 9'))
        .pipe(rename({ suffix: '.min' }))
        .pipe(csso('main.min.css'))
        .pipe(rename({ suffix: '.blessed' }))
        .pipe(bless())
        .pipe(gulp.dest('css'))
        .pipe(notify({ message: 'EdBlog Styles task complete' }));
});

// Scripts
// -------------------------------------------------
gulp.task('plugins', function() {
    gulp.src('js/plugins/*.js')
        .pipe(concat('plugins.all.js'))
        .pipe(rename({ suffix: '.min' }))
        .pipe(uglify())
        .pipe(gulp.dest('js'))
        .pipe(notify({ message: 'Plugins concat and uglify task complete' }));
});

// main.js
// -------------------------------------------------
gulp.task('mainjs', function() {
    gulp.src('js/main.js')
        .pipe(rename({ suffix: '.min' }))
        .pipe(uglify())
        .pipe(gulp.dest('js'))
        .pipe(notify({ message: 'main.js uglify task complete' }));
});

// Clean redundant files
// -------------------------------------------------
gulp.task('clean', function(cb) {
    del(['js/plugins.all.js'], cb);
});


// // Images
// gulp.task('images', function() {
//   return gulp.src('img/**/*')
//     .pipe(imagemin({ optimizationLevel: 3, progressive: true, interlaced: true }))
//     .pipe(gulp.dest('dist/images'))
//     .pipe(notify({ message: 'Images task complete' }));
// });


// Default task
// -------------------------------------------------
gulp.task('default', ['clean'], function() {
    gulp.start('styles', 'plugins', 'mainjs');
});

// Watch
// -------------------------------------------------
// -------------------------------------------------
// -------------------------------------------------
gulp.task('watch', function() {

    // Watch .scss files
    gulp.watch('scss/**/*.scss', ['styles']);

    // Watch plugins js files
    gulp.watch('js/plugins/*.js', ['plugins']);

    // Watch main.js file
    gulp.watch('js/main.js', ['mainjs']);

    // Create LiveReload server
    // livereload.listen();

    // Watch any files in _site, reload on change
    // gulp.watch(['_site/**/*']).on('change', livereload.changed);

});