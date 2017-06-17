var fs        = require('fs-extra'),
	del       = require('del'),
	args      = require('yargs'),
	gulp      = require('gulp'),
	sync      = require('browser-sync'),
	util      = require('gulp-util'),
	sass      = require('gulp-sass'),
	sftp      = require('gulp-sftp'),
	imin      = require('gulp-imagemin'),
	watch     = require('gulp-watch'),
	jsmin     = require('gulp-uglify'),
	changed   = require('gulp-changed'),
	sourcemap = require('gulp-sourcemaps');

require('gulp-load')(gulp);

var cfg   = fs.readJSONSync('config.json'),
	sync  = sync.create(),
	argv  = args.argv,
	tasks = {};

var filters = {
	'css': function(src, dst) {
		var stream = gulp.src('**/*.css', { cwd: src })
			.pipe(changed(dst))
			.pipe(gulp.dest(dst))
			.pipe(sync.stream())
		;

		return stream;
	},

	'sass': function(src, dst){
		var options = argv.production ? {
			outputStyle: 'compressed',
			precision: 8
		} : {
			outputStyle: 'uncompressed'
		};

		var stream = gulp.src('**/*.scss', { cwd: src })
			.pipe(sourcemap.init())
			.pipe(sass(options).on('error', log))
			.pipe(sourcemap.write('../maps'))
			.pipe(gulp.dest(dst))
			.pipe(sync.stream());

		return stream;
	},

	'js': function(src, dst){
		var options = argv.production ? {
			compress: true
		} : {
			compress: false
		};

		var stream = gulp.src('**/*.js', { cwd: src })
			.pipe(sourcemap.init())
			.pipe(jsmin(options).on('error', log))
			.pipe(sourcemap.write('../maps'))
			.pipe(changed(dst))
			.pipe(gulp.dest(dst))
			.pipe(sync.stream())
		;

		return stream;
	},

	'images': function(src, dst){
		var options = argv.production ? {
			optimizationLevel: 7
		} : {
			optimizationLevel: 0
		};

		var stream = gulp.src([ '**/*.gif', '**/*.png', '**/*.jpg', '**/*.jpeg', '**/*.svg' ], { cwd: src })
			.pipe(changed(dst))
			.pipe(imin(options))
			.pipe(gulp.dest(dst))
			.pipe(sync.stream())
		;

		return stream;
	},

	'html': function(src, dst) {
		var stream = gulp.src('**/*.html', { cwd: src })
			.pipe(changed(dst))
			.pipe(gulp.dest(dst))
			.pipe(sync.stream())
		;

		return stream;
	},

	'none': function(src, dst) {
		var stream = gulp.src('**/*', { cwd: src })
			.pipe(gulp.dest(dst))
			.pipe(sync.stream())
		;

		return stream;
	}
}

var log = function(error) {
	util.log(error.message);
	this.emit('end');
};


//
// Clean
//

gulp.task('clean', function() {
	for (var asset_type in cfg.app.assets) {
		del.sync(cfg.app.target + '/' + cfg.app.assets[asset_type].target + '/*', {
			force: true
		});
	}
});


//
// Initialize
//

for (var i = 0; i < cfg.app.assets.length; i++) {
	var asset = cfg.app.assets[i];
	var task  = 'build:' + asset.target;
	var src   = cfg.app.source + '/' + asset.source;
	var dst   = cfg.app.target + '/' + asset.target;

	for (var j in asset.filter) {
		gulp.task(task + ':' + asset.filter[j], (function(filter, src, dst) {
			return function() {
				return filters[filter](src, dst);
			}
		})(asset.filter[j], src, dst))
	}

	gulp.task(task, asset.filter.map(function(filter) {
		return task + ':' + filter;
	}));
}


//
// Build
//

gulp.task('build', ['clean'].concat(Object.keys(gulp.tasks).filter(function(key) {
	return key.match(/^build:[^:]+$/) ? true : false;
})));


//
// Watch
//

gulp.task('watch', function(){

	for (var i = 0; i < cfg.app.assets.length; i++) {
		var asset = cfg.app.assets[i];

		watch(cfg.app.source + '/' + asset.source + '/**/*', (function(asset) {
			return function() {
				gulp.start('build:' + asset.target);
			};
		})(asset));
	}

	watch(cfg.app.target + '/**/*', { ignoreInitial: true }, function(event) {
		if (cfg.app.sync) {
			var target         = event.path.replace(__dirname + '/', '');
			var options        = cfg.app.sync;
			options.remotePath = cfg.app.sync.basePath + '/' + target.split('/').slice(0, -1).join('/');

			gulp.src(target).pipe(sftp(options));
		}
	});
});


//
// Connect
//

gulp.task('connect', function() {
	sync.init({
		server: {
			baseDir: cfg.app.target,
			index:   cfg.app.index
		}
	});
});


//
// Default
//

gulp.task('default', ['watch', 'connect']);
