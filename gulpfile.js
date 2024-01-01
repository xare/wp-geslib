var { watch, src, dest, series } = require('gulp');
var rename = require('gulp-rename');
var sass = require('gulp-sass')(require('sass'));
var autoprefixer = require('gulp-autoprefixer');
var sourcemaps = require('gulp-sourcemaps');
var browserify = require('browserify');
var babelify = require('babelify');
var source = require('vinyl-source-stream');
var buffer = require('vinyl-buffer');
var uglify = require('gulp-uglify');
var browserSync = require('browser-sync').create();

var projectURL = 'http://larepartidora';

var styleSRC = './assets/scss/geslibAdmin.scss';
var styleFiles = [styleSRC];

var styleDIST = './dist/css/';
var styleURL = './dist/css/';
var mapURL = '../maps';

var jsSRC = './assets/js/';
var jsAdmin = 'geslibAdmin.js';
var jsPagination = 'pagination.js';
var jsUpdateValues = 'geslibUpdateValues.js';
/*var jsSlider = 'slider.js';
var jsAuth = 'auth.js' */
var jsFiles = [jsAdmin, jsPagination, jsUpdateValues];
var jsURL = './dist/js/';
var jsFolder = 'assets/js/';
var jsDIST = './dist/js/';

var jsFILES = [jsSRC];
var htmlFile = 'index.html';

var styleWatch = './assets/scss/**/*.scss';
var jsWatch = './assets/js/**/*.js';
var phpWatch = '**/*.php';

function style() {
    // compile
    return src(styleFiles)
        .pipe(sourcemaps.init())
        .pipe(sass({
            errorLogToConsole: true,
            outputStyle: 'compressed'
        }))
        .on('error', console.error.bind(console))
        .pipe(autoprefixer({
            overrideBrowserslist: ['last 2 versions'],
            cascade: false
        }))
        .pipe(rename({ suffix: '.min' }))
        .pipe(sourcemaps.write(mapURL))
        .pipe(dest(styleURL))
        .pipe(browserSync.stream());
}

async function js() {
    jsFiles.map(function(entry) {
        return browserify({
                entries: [jsSRC + entry],
                debug: true //Enable source maps
            })
            .transform( babelify, { presets: ['@babel/preset-env'] } )
            .bundle()
            .pipe( source(entry) )
            .pipe( buffer() )
            .pipe( sourcemaps.init( { loadMaps: true } ) )
            .pipe( uglify() )
            .on('error', function( err ) { console.log(err.toString()); this.emit('end'); })  // Handle errors
            .pipe( rename( { extname: '.min.js' } ) )
            .pipe( sourcemaps.write( mapURL ) )  // Write inline source maps
            .pipe( dest( jsDIST ) )
            .on( 'end', function() {
                console.info( 'Source map written to: ' + mapURL );
            })
            .pipe( browserSync.stream() );
    });
}

function browserSyncServe(cb) {
    browserSync.init({
        proxy: projectURL,
       /*  https: {
            key: 'C:\\xampp\\apache\\conf\\key\\private.key',
            cert: 'C:\\xampp\\apache\\conf\\key\\certificate.crt'
        }, */
        open: false,
        injectChanges: true,
        /* server: {
            baseDir: "./"
        } */
    });
    cb();
}

function browsersyncReload(cb) {
    browserSync.reload();
    cb();
}


function watchTask() {
    //watch(htmlWatch, browsersyncReload);
    watch(phpWatch, browsersyncReload);
    watch([styleWatch, jsWatch], series(style, js, browsersyncReload));
}
exports.default = series(
    style,
    js,
    browserSyncServe,
    watchTask
);