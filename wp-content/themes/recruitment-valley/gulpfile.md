## Setup

1. `npm install` or `yarn install`
2. Configure [`compiler.options.js`](./compiler.options.js) (see: [Compiler options documentation](./compiler.options.md))
3. Configure [`.browserslistrc`](./.browserslistrc)
4. Set babel env by creating [`.babelrc`](./.babelrc) (optional)
   ```javascript
   {
       "presets": [
           ["@babel/preset-env", { "useBuiltIns": "entry", "corejs": "2.0.0" }]
       ]
   }
   ```

## Here's some command that you can use:

1.  `npm run start` or `gulp` or `yarn start`

    This is the default command for development.

    - Watch for `.scss`, `.js`, `.php` and recompile if there's some changes
    - Compile `.scss` and `.js`
    - Reload browser automatically if there's a change

2.  `npm run build` or `yarn build` or `gulp build --production`

    Compile everything and make optimize everything for production build

3.  `gulp watch-js`

    Watch for `.js` and recompile if there's some changes

4.  `gulp compile-js`

    Compile javascript file

5.  `gulp watch-sass`

    Watch for `.sass` and recompile if there's some changes

6.  `gulp compile-sass`

    Compile SASS/SCSS file

7.  `gulp browser-sync`

    Will reload browser automatically if there's a change

#### Options

There are some options that you can use in the command line :

- `--not=file-name`

  To exclude specific file from compiling, example usage: `gulp compile-js --not-file=unused.js`, this is helpful when you are in a rush or just want to exclude once (maybe testing or etc), though we recommend you to always write it down in `compiler.options.js` if you always exclude particular file

- `--with-vendors`

  To include vendors compilation, because by default vendors would be excluded

- `--production`

  To make production ready output (with minify and without soucemaps), example usage: `gulp compile-sass --production`

## Features

- Support ES6 to all browsers (except import/export module)
- Automatic prefixer for SCSS

## Disabling notifications

Make `.env` file in your `wp-content/themes/projectname`, and insert :

```javascript
DISABLE_NOTIFIER = true;
```
