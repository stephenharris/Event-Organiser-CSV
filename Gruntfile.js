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
		},
		minify: {
			expand: true,
			cwd: 'assets/css/',				
			src: ['event_organiser_csv.css'],
			dest: 'assets/css/',
			ext: '.min.css'
		},
	},
	
	clean: {
		main: ['build/event-organiser-csv'],//Clean up build folder
		css: [ 'css/*.min.css', 'css/*-rtl.css' ],
		js: [ 'js/*.min.js' ],
		i18n: [ 'languages/*.mo', 'languages/*.pot' ] 
	},
	
	copy: {
		// Copy the plugin to a versioned build directory
		main: {
			src:  [
				'**',
				'!node_modules/**',
				'!build/**',
				'!.git/**',
				'!tests/**',
				'!vendor/**',
				'assets/**',
				'!*~',
				'!*/**/readme.*',
				'!*/**/README.*',
				'!Gruntfile.js',
				'!package.json',
				'!.gitignore',
				'!.gitmodules',
				'!assets/images/src/**',
				'!composer.lock',
				'!composer.phar',
				'!composer.json',
				'!CONTRIBUTING.md',
				'!phpunit.xml'
			],
			dest: 'build/event-organiser-csv/'
		}		
	},
		
	wp_readme_to_markdown: {
		convert:{
			options:{
				screenshot_url: 'http://ps.w.org/event-organiser-csv/assets/{screenshot}.png',
			},
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
			compare: '==',
		},
		plugin_equals_package: {
	    	version1: 'plugin',
	    	version2: '<%= pkg.version %>',
			compare: '==',
		},
	},
	
    wp_deploy: {
    	deploy:{
            options: {
        		svn_user: 'stephenharris',
        		plugin_slug: 'event-organiser-csv',
        		build_dir: 'build/event-organiser-csv/'
            },
    	}
    },
		
	phpunit: {
		classes: {
			dir: 'tests/unit-tests'
		}
	},
});
	
	// Default task(s).
	grunt.registerTask( 'default', ['jshint', 'uglify', 'cssmin' ] );
		
	grunt.registerTask( 'test', [ 'jshint', 'checktextdomain', 'phpunit' ] );

	grunt.registerTask( 'build', [ 'test', 'clean', 'uglify', 'cssmin', 'pot', 'po2mo', 'wp_readme_to_markdown', 'copy' ] );

	grunt.registerTask( 'deploy', [ 'checkwpversion', 'checkbranch:master', 'checkrepo:deploy', 'build', 'wp_deploy' ] );
	
	grunt.util.linefeed = '\n';
};
