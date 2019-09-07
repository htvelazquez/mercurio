/*jshint strict:false */

//npm install gulp gulp-bump semver yargs gulp-clean gulp-zip gulp-string-replace fs --save-dev

var gulp = require('gulp'),
	bump = require('gulp-bump'),
	semver = require('semver'),
	args = require('yargs').argv,
	clean = require('gulp-clean'),
	zip = require('gulp-zip'),
	replace = require('gulp-string-replace'),
	fs = require('fs');

var local_cirenio_url =  '0.0.0.0:8011',
	qa_cirenio_url =  'sniffer.cirenio.com',
	prod_cirenio_url =  'sniffer.cirenio.com';

var remoteConfigFile = 'src/js/remoteConfig.js',
	manifestFile = './src/manifest.json';

//clean build directory
gulp.task('clean', function() {
	return gulp.src('build/*', {read: false})
		.pipe(clean());
});

//copy static folders to build directory
gulp.task('copy', ['clean'], function() {
	var stream = gulp.src(['!'+remoteConfigFile,'src/**/*'])
		.pipe(gulp.dest('build'));

	return stream;
});

// bump versions on manifest
gulp.task('setversion', function () {
	var manifest = JSON.parse(fs.readFileSync(manifestFile));

	var type = args.type;
    var version = args.version;
    //var options = {};
    var newVer;
    if (version) {
        newVer = version;
    } else {
		newVer = semver.inc(manifest.version, type);
    }

  return gulp.src([manifestFile])
    .pipe(bump({
      version: newVer
    }))
    .pipe(gulp.dest('./src'));
});

gulp.task('setconfig', ['copy'], function () {
	var env = args.env;

	var destFile =  'build/js';

	switch(env) {
    	case 'production':
		case 'prod':
			return gulp.src(remoteConfigFile)
				.pipe(replace( '\/' + local_cirenio_url , '\/' + prod_cirenio_url))
				.pipe(replace( '\/' + qa_cirenio_url , '\/' + prod_cirenio_url))
\				.pipe(gulp.dest(destFile));

    	case 'qa':
		case 'test':
			return gulp.src(remoteConfigFile)
		  	    .pipe(replace( '\/' + local_cirenio_url , '\/' + qa_cirenio_url))
				.pipe(replace( '\/' + prod_cirenio_url, '\/' + qa_cirenio_url))
		  	    .pipe(gulp.dest(destFile));

		case 'dev':
		case 'local':
			return gulp.src(remoteConfigFile)
				.pipe(replace( '\/' + prod_cirenio_url, '\/' + local_cirenio_url))
				.pipe(replace( '\/' + qa_cirenio_url, '\/' + local_cirenio_url))
				.pipe(gulp.dest(destFile));

    	default:
			return gulp.src(remoteConfigFile)
				.pipe(gulp.dest(destFile));
	}
	return stream;
});

gulp.task('build', ['clean','setversion','copy','setconfig'], function() {

	var manifest = JSON.parse(fs.readFileSync(manifestFile)),
		distFileName = 'Cirenio Contacts Solutions v' + manifest.version + '.zip';

	//build distributable extension
	return gulp.src('build/**')
		.pipe(zip(distFileName))
		.pipe(gulp.dest('dist'));
});

gulp.task('default', function() {
	/// <summary>
    /// Usage:
    /// 1. gulp build : bumps the manifest.json.
    ///   i.e. from 0.1.1 to 0.1.2
    /// 2. gulp build --version 1.1.1 : bumps/sets the package.json and bower.json to the
    ///    specified revision.
    /// 3. gulp build --type major       : bumps 1.0.0
    ///    gulp build --type minor       : bumps 0.1.0
    ///    gulp build --type patch       : bumps 0.0.2
    ///    gulp build --type prerelease  : bumps 0.0.1-2
    /// 4. gulp build					 : set the env variables to local enviroment
	///	   gulp build --env dev 		 : set the env variables to local enviroment
	///	   gulp build --env local 		 : set the env variables to local enviroment
	///    gulp build --env production	 : set the env variables to Production enviroment
	///    gulp build --env prod	 	 : set the env variables to Production enviroment
	///    gulp build --env qa	 		 : set the env variables to QA enviroment
	///    gulp build --env test	 	 : set the env variables to QA enviroment
    /// </summary>

    gulp.start('build');
});
