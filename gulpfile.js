/* eslint indent: ["error", 4] */
// load gulp dependancies
var gulp = require('gulp'),
    noop = require('gulp-noop'),
    argv = require('yargs').argv,
    autoprefixer = require('gulp-autoprefixer'),
    browserSync = require('browser-sync').create(),
    concat = require('gulp-concat'),
    csso = require('gulp-csso'),
    mqpacker = require('css-mqpacker'),
    postcss = require('gulp-postcss'),
    sass = require('gulp-sass'),
    sourcemaps = require('gulp-sourcemaps'),
    terser = require('gulp-terser'),
    newer = require('gulp-newer'),
    path = require('path');


// path variables
var config = {
    distPHP: './public/course/wp-content/',
    distTheme: './public/course/wp-content/themes/eduiland',
    distPlugin: './public/course/wp-content/plugins/eduiland',
    assets: './source/assets/*.{png,jpg,svg}',
    proxy: 'edu2.inventionlandinstitute.test',
    css: {
        sass: './resources/scss/**/*.scss',
        framework: './node_modules/bootstrap/scss'
    },
    jscripts: {
        src: ['./resources/js/app.wp.js']
        // src: [
        //     './node_modules/bootstrap/dist/js/bootstrap.bundle.js',
        //     './resources/jswp/app.jqextensions.js',
        //     './resources/jswp/app.wp.js',
        //     './resources/jswp/**/*.js'
        // ]
    }
};

// initialize browsersync
var browser = function() {
    browserSync.init({
        proxy: config.proxy,
        browser: 'google chrome',
        notify: false
    });
};


// watch for events
var watcher = function() {
    var watchCSS = gulp.watch(config.css.sass);
    var watchJS = gulp.watch(config.jscripts.src);
    watchCSS.on('all', buildCSS);
    watchJS.on('all', buildJS);
    if(argv.browsersync) {
        var watchPHP = gulp.watch(config.distPHP + '/**/*.php');
        watchPHP.on('change', browserSync.reload);
    }
};


// build JS Footer
var buildJS = function() {
    return gulp
        .src(config.jscripts.src)
        .pipe(newer(config.distPlugin + '/assets/js/eduiland_wp.bundle.js'))
        .pipe(sourcemaps.init())
        .pipe(concat('eduiland_wp.bundle.js'))
        .pipe(argv.production ? terser() : noop())
        .pipe(
            argv.debugger
                ? sourcemaps.write({
                    includeContent: false,
                    sourceRoot: function(file) {
                        //return path.relative(path.join(file.cwd, file.path), file.base);
                        const absFilePath = file.path;
                        const absSrcRoot = path.resolve(config.jscripts.src);
                        const relToRoot = path.relative(absSrcRoot, absFilePath);
                        const absDstRoot = path.resolve(config.dist);
                        const absDstFilePath = path.join(absDstRoot, relToRoot);
                        const relToSrcRoot = path.relative(absDstFilePath, absSrcRoot);
                        return relToSrcRoot;
                    }
                })
                : argv.production
                    ? noop()
                    : sourcemaps.write('./')
        )
        .pipe(gulp.dest(config.distPlugin + '/assets/js'))
        .pipe(argv.browsersync ? browserSync.stream() : noop());
};


// Build CSS files
var buildCSS =  function() {
    return gulp.src(config.css.sass)
        .pipe(sourcemaps.init())
        .pipe(sass({
            includePaths: [config.css.framework]
        }).on('error', sass.logError))
        .pipe(postcss([mqpacker({sort:true})]))
        .pipe(autoprefixer({
            browsers: ['last 3 versions', '> 5%', 'ie >= 8'],
            add: true
        }))
        .pipe(argv.production ? csso() : noop())
        .pipe(argv.production ? noop() : sourcemaps.write('./'))
        .pipe(gulp.dest(config.distTheme + '/assets/css'))
        .pipe(argv.browsersync ? browserSync.stream() : noop());
};


// Default and build tasks
//argv.browsersync ? gulp.task('default', ['watch', 'browser-sync']) : gulp.task('default', ['watch']);
argv.browsersync ? gulp.task('default', gulp.parallel(watcher, browser)) : gulp.task('default', gulp.parallel(watcher));

gulp.task('build', gulp.parallel(buildCSS, buildJS));
//gulp.task('build', gulp.parallel(buildCSS));
gulp.task('css', gulp.parallel(buildCSS));
gulp.task('js', gulp.parallel(buildJS));
