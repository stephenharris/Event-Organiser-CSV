module.exports = function( grunt ) {

require('load-grunt-tasks')(grunt);
	
// Project configuration
grunt.initConfig( {
	pkg:    grunt.file.readJSON( 'package.json' ),
		
	uglify: {
		options: {
			compress: {
				global_defs: {
					"EO_SCRIPT_DEBUG": false
				},
				dead_code: true
			},
			banner: '/*! <%= pkg.name %> <%= pkg.version %> */\n'
		},
		build: {
			files: [
			        {
			        	expand: true,// Enable dynamic expansion.
			        	src: ['assets/js/*.js', '!assets/js/*.min.js' ],// Actual pattern(s) to match.
			        	ext: '.min.js'// Dest filepaths will have this extension.
			        }
			]
		}
	},
		
	jshint: {
		all: [
			'assets/js/*.js',
			'!assets/js/*.min.js',
			'!assets/js/vendor/*.js'
		],
		options: {
			curly:   true,
			eqeqeq:  true,
			immed:   true,
			latedef: true,
			newcap:  true,
			noarg:   true,
			sub:     true,
			undef:   true,
			boss:    true,
			eqnull:  true,
			globals: {
				exports: true,
				module:  false
			}
		}		
	},
		
	cssmin: {
		options: {
			banner: '/*! <%= pkg.title %> - v<%= pkg.version %>\n' +
				' * <%= pkg.homepage %>\n' +
				' * Copyright (c) <%= grunt.template.today("yyyy") %>;' +
				' * Licensed GPLv2+' +
				' */\n'
			,
			minify: {
				expand: true,
				cwd: 'assets/css/',				
				src: ['event_organiser_csv.css'],
				dest: 'assets/css/',
				ext: '.min.css'
			}
		},
	},
	
	clean: {
		main: ['build/<%= pkg.version %>']
	},
	
	copy: {
		// Copy the plugin to a versioned build directory
		main: {
			src:  [
				'**',
				'!node_modules/**',
				'!build/**',
				'!.git/**',
				'!*~',
				'!*/**/readme.*',
				'!*/**/README.*',
				'!Gruntfile.js',
				'!package.json',
				'!.gitignore',
				'!.gitmodules'
			],
			dest: 'build/<%= pkg.version %>/'
		}		
	},
		
	compress: {
		main: {
			options: {
				mode: 'zip',
				archive: './build/event_organiser_csv.<%= pkg.version %>.zip'
			},
			expand: true,
			cwd: 'build/<%= pkg.version %>/',
			src: ['**/*'],
			dest: 'event_organiser_csv/'
		}		
	},
	

	wp_readme_to_markdown: {
		convert:{
			files: {
				'readme.md': 'readme.txt'
			},
		},
	},
	
	po2mo: {
		files: {
    			src: 'languages/*.po',
			expand: true,
		},
	},

	pot: {
		options:{
        	text_domain: 'event-organiser-csv',
	        dest: 'languages/',
			keywords: [
				'__:1',
				'_e:1',
				'_x:1,2c',
				'esc_html__:1',
				'esc_html_e:1',
				'esc_html_x:1,2c',
				'esc_attr__:1', 
				'esc_attr_e:1', 
				'esc_attr_x:1,2c', 
				'_ex:1,2c',
				'_n:1,2', 
				'_nx:1,2,4c',
				'_n_noop:1,2',
				'_nx_noop:1,2,3c'
			],
			},
    	files:{
		src:  [
			'**/*.php',
			'!node_modules/**',
			'!build/**',
			'!tests/**',
			'!vendor/**',
			'!*~',
		],
	expand: true,
		}
	},

	checktextdomain: {
		options:{
			text_domain: 'event-organiser-csv',
			correct_domain: true,
			keywords: [
			'__:1,2d',
			'_e:1,2d',
			'_x:1,2c,3d',
			'esc_html__:1,2d',
			'esc_html_e:1,2d',
			'esc_html_x:1,2c,3d',
			'esc_attr__:1,2d', 
			'esc_attr_e:1,2d', 
			'esc_attr_x:1,2c,3d', 
			'_ex:1,2c,3d',
			'_n:1,2,4d', 
			'_nx:1,2,4c,5d',
			'_n_noop:1,2,3d',
			'_nx_noop:1,2,3c,4d'
			],
		},
		files: {
			src:  [
			'**/*.php',
			'!node_modules/**',
			'!build/**',
			'!tests/**',
			'!*~',
			],
			expand: true,
		},
	},

	checkrepo: {
		deploy: {
			tag: {
				eq: '<%= pkg.version %>',    // Check if highest repo tag is equal to pkg.version
			},
			tagged: true, // Check if last repo commit (HEAD) is not tagged
			clean: true,   // Check if the repo working directory is clean
    	}
	},

	checkwpversion: {
		plugin_equals_stable: {
	    	version1: 'plugin',
	    	version2: 'readme',
			compare: '!=',
		},
		plugin_equals_package: {
	    	version1: 'plugin',
	    version2: '<%= pkg.version %>',
			compare: '==',
		},
	},
		
});
	
	// Default task(s).
	grunt.registerTask( 'default', ['jshint', 'uglify', 'cssmin' ] );
		
	grunt.registerTask( 'test', [ 'jshint', 'checktextdomain' ] );

	grunt.registerTask( 'build', [ 'test', 'newer:uglify', 'newer:cssmin', 'newer:pot', 'newer:po2mo', 'wp_readme_to_markdown', 'clean', 'copy' ] );

	grunt.registerTask( 'deploy', [ 'checkwpversion', 'checkbranch:master', 'checkrepo:deploy', 'build', 'compress' ] );
	
	grunt.util.linefeed = '\n';
};
