const Encore = require('@symfony/webpack-encore');
const path = require('path');

if (!Encore.isRuntimeEnvironmentConfigured()) {
    Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

Encore
    .setOutputPath('public/build/')
    .setPublicPath('/build')
    .addEntry('app', './assets/app.js')
    .splitEntryChunks()
    .enableSingleRuntimeChunk()
    .cleanupOutputBeforeBuild()
    .enableBuildNotifications()
    .enableSourceMaps(!Encore.isProduction())
    .enableVersioning(Encore.isProduction())

    // Enable Vue.js
    .enableVueLoader(() => {}, {
        version: 3,
        runtimeCompilerBuild: false,
        useJsx: false
    })

    // Enable SASS/SCSS
    .enableSassLoader()

    // Configure Babel
    .configureBabel((config) => {
        config.plugins.push('@babel/plugin-proposal-class-properties');
    })

    // Configure aliases for imports
    .addAliases({
        '@': path.resolve(__dirname, 'assets'),
        'vue$': 'vue/dist/vue.esm-bundler.js'
    })

    // Configure dev server
    .configureDevServerOptions(options => {
        options.allowedHosts = 'all';
        options.https = false;
        options.hot = true;
        options.client = {
            overlay: {
                errors: true,
                warnings: false,
            },
        };
        options.historyApiFallback = {
            disableDotRule: true,
        };
    })

    // Copy images
    .copyFiles({
        from: './assets/images',
        to: 'images/[path][name].[ext]',
    })

    // enables and configure @babel/preset-env polyfills
    .configureBabelPresetEnv((config) => {
        config.useBuiltIns = 'usage';
        config.corejs = '3.38';
    })

    // enables Sass/SCSS support
    .enableSassLoader()

    // enable Vue.js support
    .enableVueLoader()

    // uncomment if you use TypeScript
    //.enableTypeScriptLoader()

    // uncomment to get integrity="..." attributes on your script & link tags
    // requires WebpackEncoreBundle 1.4 or higher
    //.enableIntegrityHashes(Encore.isProduction())

    // uncomment if you're having problems with a jQuery plugin
    //.autoProvidejQuery()
;

module.exports = Encore.getWebpackConfig();
