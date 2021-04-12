const mix = require('laravel-mix'),
    argv = require('yargs').argv,
    mqpacker = require('css-mqpacker'),
    path = require('path'),
    autoprefixer = require('autoprefixer'),
    ChunkRenamePlugin = require('webpack-chunk-rename-plugin'),
    exec = require('child_process').exec;

// IE 11:
// TargetsPlugin = require('targets-webpack-plugin');
// require('laravel-mix-polyfill');

// IE 11:
// npm install --save @babel/polyfill core-js
// npm install --save-dev laravel-mix-polyfill targets-webpack-plugin

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

// Code Splitting / Dynamic  Imports / IE 11 babel
mix.webpackConfig({
    output: {
        publicPath: '/assets/',
        filename: '[name].js',
        chunkFilename: 'js/chunks/[name].js'
        //devtoolModuleFilenameTemplate: '[absolute-resource-path]' // Needed for vscode's launch.json
    },
    plugins: [
        new ChunkRenamePlugin({
            initialChunksWithEntry: true,
            '/js/vendor': '/js/vendor.js'
        }),
        {
            apply: compiler => {
                compiler.hooks.done.tap('DonePlugin', () => {
                    exec('./manifest.sh', (err, stdout, stderr) => {
                        stdout && process.stdout.write(stdout);
                        stderr && process.stderr.write(stderr);
                    });
                });
            }
        }
    ]
});

mix.babelConfig({
    plugins: ['@babel/plugin-syntax-dynamic-import']
});

//mix.sass('resources/scss/site.scss', 'public/course/wp-content/themes/eduiland/assets/css')

// Fix output location
mix.setResourceRoot(path.normalize('/assets/'))
    .setPublicPath(path.normalize('public/assets'))

// Resource
mix.js('resources/js/app.lvl.js', 'public/assets/js/app.lvl.js')
    .extract(['vue', 'jquery', 'popper.js', 'bootstrap', 'axios'])
    .sourceMaps(false, 'source-map');

// Wordpress scripts
// mix.scripts('resources/js/app.wp.js', 'public/course/wp-content/themes/eduiland/assets/js/eduiland_wp.bundle.js')

// Cache bust for production / polyfill IE 11
if (mix.inProduction()) {
    mix.version()
    // IE 11:
    // .webpackConfig({
    //     plugins: [
    //         new TargetsPlugin({
    //             browsers: ['last 2 versions', 'chrome >= 41', 'IE 11']
    //         })
    //     ]
    // })
    // .polyfill({
    //     enabled: true,
    //     useBuiltIns: 'usage',
    //     targets: { ie: 11 },
    //     debug: true,
    //     corejs: 3
    // });
}

// Remove console if noconsole argument passed
if (mix.inProduction() && argv.noconsole) {
    mix.options({
        terser: {
            terserOptions: {
                compress: {
                    drop_console: true
                }
            }
        }
    });
}


// Turn on browserSync
argv.browsersync &&
    mix.browserSync({
        proxy: 'edu2.inventionlandinstitute.test',
        //browser: 'Google Chrome',
        open: false,
        notify: false
        // callbacks: {
        //     ready: function(err, bs) {
        //         require('opn')(bs.options.getIn(['urls', 'local']), {
        //             app: ['google chrome', ' --remote-debugging-port=9229']
        //         });
        //     }
        // }
    });

// Group css media queries
mix.options({
    processCssUrls: false,
    postCss: [
        mqpacker({
            sort: true
        }),
        autoprefixer
    ]
})


// Get access to sass vars
mix.webpackConfig({
    // resolve: {
    //     extensions: ['.js', '.vue', '.json'],
    //     alias: {
    //         '@': __dirname + '/resources/js'
    //     }
    // },
    module: {
        rules: [{
            test: /\.scss$/,
            use: [{
                loader: 'sass-loader',
                options: {
                    includePaths: [
                        path.resolve(__dirname, './node_modules/bootstrap/scss/')
                    ],
                    data: `
                        @import "./resources/scss/settings";
                        @import "./node_modules/bootstrap/scss/functions";
                        @import "./node_modules/bootstrap/scss/variables";
                        @import "./node_modules/bootstrap/scss/vendor/rfs";
                    `
                }
            }]
        }]
    }
});
