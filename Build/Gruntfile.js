const sass = require('sass-embedded');
module.exports = function(grunt) {

  grunt.initConfig({

    pkg: grunt.file.readJSON('package.json'),
    extName: 'iconpack',
    paths: {
      root: '../',
      sources: 'Sources/',
      sourceCss: '<%= paths.sources %>Scss/',
      sourceHtml: '<%= paths.sources %>Pug/',
      sourceJs: '<%= paths.sources %>TypeScript/',
      resources: '<%= paths.root %>Resources/',
      resourcesPrivate: '<%= paths.resources %>Private/',
      resourcesPublic: '<%= paths.resources %>Public/'
    },

    // Compile TypeScript
    exec: {
      tsv11: ((process.platform === 'win32') ? 'node_modules\\.bin\\tsc.cmd' : './node_modules/.bin/tsc') + ' --project <%= paths.sourceJs %>v11/tsconfig.json',
      tsv12: ((process.platform === 'win32') ? 'node_modules\\.bin\\tsc.cmd' : './node_modules/.bin/tsc') + ' --project <%= paths.sourceJs %>v12/tsconfig.json',
      'npm-install': 'npm install --save-dev'
    },

    // Minify JavaScript
    uglify: {
      options: {
        output: {
          comments: false,
          quote_style: 1
        },
        compress: {
          drop_console: true,
          negate_iife: false
        },
        banner: '/*\n * This file is part of the "<%= extName %>" Extension for TYPO3 CMS.\n *\n * Conceived and written by <%= pkg.author %>\n *\n * For the full copyright and license information, please read the\n * LICENSE.txt file that was distributed with this source code.\n */'
      },
      main: {
        expand: true,
        cwd: '<%= paths.resourcesPublic %>JavaScript/',
        src: ['**/*.js', '!**/lang/*.js'],
        dest: '<%= paths.resourcesPublic %>JavaScript/'
      }
    },

    // Compile SCSS
    sass: {
      options: {
        implementation: sass,
        outputStyle: 'expanded',
        precision: 8,
        sourceMap: false,
        silenceDeprecations: [
          'legacy-js-api'
        ]
      },
      main: {
        files: [{
          expand: true,
          cwd: '<%= paths.sourceCss %>',
          src: ['**/*.scss'],
          dest: '<%= paths.resourcesPublic %>Css/',
          ext: '.min.css'
        }]
      }
    },

    // Minify CSS
    cssmin: {
      options: {
        advanced: false
      },
      main: {
        expand: true,
        cwd: '<%= paths.resourcesPublic %>Css/',
        src: ['**/*.css'],
        dest: '<%= paths.resourcesPublic %>Css/'
      }
    },

    // Compile HTML
    pug: {
      options: {
        data: {
          debug: true
        },
        client: false,
        pretty: true
      },
      main: {
        files: [{
          expand: true,
          cwd: '<%= paths.sourceHtml %>',
          src: ['**/*.pug'],
          dest: '<%= paths.resourcesPrivate %>',
          ext: '.html'
        }]
      }
    },

    // Prettify HTML
    jsbeautifier: {
      options: {
        html: {
          braceStyle: 'collapse',
          indentChar: ' ',
          indentScripts: 'keep',
          indentWithTabs: true,
          indentSize: 2,
          maxPreserveNewlines: 3,
          preserveNewlines: true,
          unformatted: ['a', 'sub', 'sup', 'b', 'i', 'u'],
          wrapLineLength: 0,
          endWithNewline: true,
          extraLiners: 'head,body,/html,f:layout'
        }
      },
      main: {
        expand: true,
        cwd: '<%= paths.resourcesPrivate %>',
        src: ['**/*.html'],
        ext: '.html'
      },
    },

    // Add newline at end of CSS files if missing
    endline: {
      main: {
        expand: true,
        cwd: '<%= paths.resourcesPublic %>Css/',
        src: ['**/*.css'],
        dest: '<%= paths.resourcesPublic %>Css/'
      }
    }
  });

  /**
   * Removes TypeScript caches
   */
  grunt.registerTask('clear-ts-cache', function() {
    const versions = [11, 12];
    versions.forEach((version) => {
      let cacheDir = grunt.config('paths.sourceJs') + 'v' + version + '/.cache';
      if (grunt.file.isDir(cacheDir)) {
        grunt.file.delete(cacheDir);
      }
    });
  });

  grunt.loadNpmTasks('grunt-contrib-cssmin');
  grunt.loadNpmTasks('grunt-contrib-pug');
  grunt.loadNpmTasks('grunt-contrib-uglify');
  grunt.loadNpmTasks('grunt-endline');
  grunt.loadNpmTasks('grunt-exec');
  grunt.loadNpmTasks('grunt-jsbeautifier');
  grunt.loadNpmTasks('grunt-sass');

  grunt.registerTask('clear', ['clear-ts-cache']);
  grunt.registerTask('css', ['sass', 'cssmin', 'endline']);
  grunt.registerTask('html', ['pug', 'jsbeautifier']);
  grunt.registerTask('js', ['exec:tsv11', 'exec:tsv12', 'uglify']);
  grunt.registerTask('build', ['clear', 'js', 'css', 'html']);
  grunt.registerTask('build-dev', ['clear', 'exec:tsv11', 'exec:tsv12', 'sass', 'pug']);

  grunt.registerTask('default', ['build']);
};
