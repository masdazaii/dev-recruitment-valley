/**
 * @type {import("compiler.options").CompilerOptionsConfiguration}
 */
const configuration = {
    sass: {
        src: [
            "./assets/css/src/wp-admin.scss",
            "./assets/css/src/wp-editor.scss",
            "./assets/css/src/wp-login.scss",
            "./assets/css/src/themes.scss",
        ],
        watch: ["./assets/**/*.scss"],
        distFolder: "./assets/css/dist",
        minify: true,
        sourcemap: true,
    },
    javascript: [
        {
            name: "themes",
            src: "./assets/js/src/themes/index.js",
        },
    ],
    javascriptClassic: {
        list: [
            {
                name: "themes",
                src: [
                    "./assets/js/src/themes/homepage.js",
                    "./assets/js/src/themes/quiz.js",
                ],
            },
            {
                name: "vendors",
                src: [
                    "./assets/js/src/themes/homepage.js",
                    "./assets/js/src/themes/quiz.js",
                ],
            },
            {
                name: "wp-admin",
                src: ["./assets/js/src/wp-admin.js"],
                polyfill: false,
            },
            {
                name: "wp-login",
                src: ["./assets/js/src/wp-login.js"],
                polyfill: false,
            },
        ],
        watch: ["./assets/js/src/**/*.js"],
        distFolder: "./assets/js/dist",
        minify: true,
        sourcemap: true,
    },
    browserSync: {
        watch: [
            "./assets/css/dist/*.min.css",
            "./assets/js/dist/*.min.js",
            "./**/*.php",
        ],
        config: {
            proxy: "http://project-starter.local/",
            host: "project-starter.local",
            watchTask: true,
            open: "external",
        },
    },
};

module.exports = configuration;
