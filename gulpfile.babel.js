import gulp from 'gulp';
import sass from 'gulp-sass';
import dartSass from 'sass';
import autoprefixer from 'gulp-autoprefixer';
import sourcemaps from 'gulp-sourcemaps';

const scss = sass(dartSass);

export function styles() {
  return gulp.src('./folklore.scss')         // input SCSS
    .pipe(sourcemaps.init())
    .pipe(scss().on('error', scss.logError))
    .pipe(autoprefixer())
    .pipe(sourcemaps.write('.'))
    .pipe(gulp.dest('./'));                  // output CSS in same folder
}

export function watch() {
  gulp.watch('./folklore.scss', styles);
}

export default gulp.series(styles, watch);
