module.exports = function(grunt) {
    grunt.initConfig({
        removelogging: {
            dist: {
                src: "assets/js/app.js",
                dest: "assets/js/app.js",
            }
        },
        uglify: {
            production: {
                files: {
                    'assets/js/app.min.js': ['assets/js/app.js']
                }
            }
        },
        concat: {
            development: {
                files: {
                    'assets/js/app.js': [
                        'assets/js/lib/jquery.magnific-popup.min.js',
                        'assets/js/lib/owl.carousel.min.js',
                        'assets/js/lib/waypoints.js',
                        'assets/of/of-assets/js/of-master.js',
                        'assets/of/components/of-sidebar-menu-advanced/of-sidebar-menu-advanced.min.js',
                        'assets/js/source/app.dev.js',
                        'assets/js/source/faq.js'
                    ]
                }
            }
        },
        autoprefixer: {
          options: {
            browsers: ['last 3 versions', 'ie 9']
          },
          single_file: {
            options: {
            },
            src: 'assets/css/style.css',
            dest: 'assets/css/style.css'
          }
        },
        sass: {
            development: {
                options: {
                    style: 'expanded'
                },
                files: {
                    'assets/css/style.css': [
                        'assets/css/scss/style.scss'
                    ]
                }
            },
            production: {
                options: {
                    style: 'compressed'
                },
                files: {
                    'assets/css/style.min.css': [
                        'assets/css/scss/style.scss'
                    ]
                }

            }
        },
        watch: {
            options: {
                livereload: true
            },
            scripts: {
                files: ['assets/**/*.js'],
                tasks: ['concat:development'],
                options: {
                    spawn: false
                }
            },
            css: {
                files: ['assets/**/*.scss'],
                tasks: ['sass:development', 'autoprefixer'],
                options: {
                    spawn: false
                }
            },
            svg: {
                files: ['assets/images/icons/*.svg'],
                tasks: ['svgstore'],
                options: {
                    spawn: false
                }
            }
        },
        setPHPConstant: {
            stage: {
                constant: 'PRODUCTION_MODE',
                value: 'true',
                file: 'lib/class-sk-init.php'
            },
            development: {
                constant: 'PRODUCTION_MODE',
                value: 'false',
                file: 'lib/class-sk-init.php'
            }
        },
        svgstore: {
            options: {
                formatting: {
                    indent_size: 2
                },
                prefix: '',
                svg: {
                    'version': '1.1',
                    'xmlns': 'http://www.w3.org/2000/svg',
                    'xmlns:xlink': 'http://www.w3.org/1999/xlink'
                },
                cleanup: ['fill', 'stroke', 'fill-rule', 'clip-rule']
            },
            default: {
                files: {
                    'assets/images/icons.svg': ['assets/images/icons/*.svg']
                }
            }
        },
        rename: {
            components: {
                files: [{
                    expand: true,
                    dot: true,
                    cwd: 'assets/of/components',
                    dest: 'assets/of/components/',
                    src: [
                        '**/{,*/}*.css'
                    ],
                    rename: function(dest, src) {
                        return dest + src.replace('.css','.scss');
                    }
                },
                {src: ['assets/of/of-assets/css/of-master.min.css'], dest: 'assets/of/of-assets/css/of-master.min.scss'}]
            }
        }
    });

    grunt.loadNpmTasks('grunt-contrib-rename');
    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-concat');
    grunt.loadNpmTasks('grunt-contrib-watch');
    grunt.loadNpmTasks('grunt-contrib-sass');
    grunt.loadNpmTasks('grunt-php-set-constant');
    grunt.loadNpmTasks('grunt-remove-logging');
    grunt.loadNpmTasks('grunt-svgstore');
    grunt.loadNpmTasks('grunt-autoprefixer');

    grunt.registerTask('release', ['setPHPConstant:stage', 'removelogging', 'uglify:production', 'sass:production']);
    grunt.registerTask('default', ['setPHPConstant:development', 'watch']);
};