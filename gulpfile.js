const elixir = require('laravel-elixir');

require('laravel-elixir-vue');

/*
 |--------------------------------------------------------------------------
 | Elixir Asset Management
 |--------------------------------------------------------------------------
 |
 | Elixir provides a clean, fluent API for defining some basic Gulp tasks
 | for your Laravel application. By default, we are compiling the Sass
 | file for our application, as well as publishing vendor resources.
 |
 */

elixir(function(mix) {
    mix.version(
        [
            'js/common-scripts.js',
            'js/format_number.js',
            'js/dashboard.js',
            'js/purchases.js',
            'js/cash_sales_js.js',
            'js/customer_feedback.js',
            'js/problem_report.js',
            'js/cred_sales_js.js',
            'js/labour_hours_js.js',
            'js/lodgements.js',
            'js/operations-scorecard.js',
            'js/trading_account_js.js',
            'js/stock_control_js.js',
            'js/vend_sales_js.js',
            'css/style.css',
            'css/style-responsive.css',
            'css/custom.css',
            'css/dashboard.css'
        ]
    );
});
