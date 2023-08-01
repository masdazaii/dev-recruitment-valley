### SASS Options Object

| Option     | Type    | Default | Description                       |
| ---------- | ------- | ------- | --------------------------------- |
| src        | array   | []      | list of scss input file           |
| watch      | array   | []      | list of file that will be watched |
| distFolder | string  | ""      | destination for output bundle     |
| minify     | boolean | false   | whether use minify or not         |
| sourcemap  | boolean | false   | whether use sourcemap or not      |

> ##### Note:
>
> Browser Support defined in [`.browserslistrc`](.browserslistrc)

##### Example SASS Options

```javascript
module.exports = {
	sass: {
		src: [
			"./assets/css/src/wp-admin.scss",
			"./assets/css/src/wp-login.scss",
			"./assets/css/src/styles.scss"
		],
		watch: [
			"./assets/css/src/**/*.scss",
			"!./assets/css/src/not-watch.scss"
		],
		distFolder: "./assets/css/dist",
		minify: true,
		sourcemap: true
	}
};
```

### Javascript Options Object

| Option     | Type                                                                   | Default | Description                       |
| ---------- | ---------------------------------------------------------------------- | ------- | --------------------------------- |
| list       | array of [jslist](#jslist) or a string (string only for a single file) | []      | list of bundle                    |
| watch      | array                                                                  | []      | list of file that will be watched |
| distFolder | string                                                                 | ""      | destination for output bundle     |
| minify     | boolean                                                                | false   | whether use minify or not         |
| sourcemap  | boolean                                                                | false   | whether use sourcemap or not      |

> ##### Note:
>
> Browser Support defined in [`.browserslistrc`](.browserslistrc)

<h5 id="jslist">JS List Object</h5>

| Key      | Type    | Default | Description                       |
| -------- | ------- | ------- | --------------------------------- |
| name     | string  | ""      | Name for output bundle file       |
| src      | array   | []      | List of input file for the bundle |
| polyfill | boolean | true    | Whether use polyfill or not       |

> ##### Note:
>
> Without minify: Polyfill will increase file size up to _164kB_  
> With minify : Polyfill will increase file size up to _94kB_

##### Example Javascript Options

```javascript
module.exports = {
	javascript: {
		list: [
			// Single file with default option
			"./assets/js/src/single-file.js",
			{
				name: "themes",
				src: [
					"./assets/js/src/themes/home.js",
					"./assets/js/src/themes/about.js"
				]
			},
			{
				name: "vendors",
				src: [
					"./node_modules/jquery/dist/jquery.js",
					"./assets/js/src/vendors/jquery-ui.js"
				],
				polyfill: false
			}
		],
		watch: ["./assets/js/src/**/*.js", "!./assets/js/src/not-watch.js"],
		distFolder: "./assets/js/dist",
		minify: true,
		sourcemap: true
	}
};
```

### BrowserSync Options Object

| Option | Type                                                            | Default | Description                       |
| ------ | --------------------------------------------------------------- | ------- | --------------------------------- |
| watch  | array                                                           | []      | list of file that will be watched |
| config | [browser-sync options](https://www.browsersync.io/docs/options) |         | browser sync options              |

> #### Note:
>
> You should change `proxy` and `host` options to URL of your local server

##### Example BrowserSync Config

```javascript
module.exports = {
	browserSync: {
		watch: ["./assets/**/*.min.css", "./assets/**/*.min.js", "./**/*.php"],

		// Available config options
		// https://www.browsersync.io/docs/options
		config: {
			proxy: "http://projectname.local/",
			host: "projectname.local",
			watchTask: true,
			open: "external"
		}
	}
};
```
