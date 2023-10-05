import browserSync from "browser-sync";

export type CompilerOptionsConfiguration = {
	sass: {
		/**
		 * File path
		 */
		src: Array<string | { name: string; src: string }>;

		/**
		 * File path / Glob pattern
		 */
		watch: Array<string>;

		/**
		 * File path
		 */
		distFolder: string;
		minify: boolean;
		sourceMap: boolean;
	};

	/**
	 * File path
	 */
	javascript: Array<string | { name: string; src: string }>;

	javascriptClassic: {
		list: Array<{
			name: string;

			/**
			 * File path / Glob pattern
			 */
			src: Array<string>;
			polyfill: boolean;
		}>;

		/**
		 * File path / Glob pattern
		 */
		watch: Array<string>;

		/**
		 * File path
		 */
		distFolder: string;
		minify: boolean;
		sourceMap: boolean;
	};
	browserSync: {
		/**
		 * File path / Glob pattern
		 */
		watch: Array<string>;

		/**
		 * BrowserSync configuration
		 * see: https://www.browsersync.io/docs/options
		 */
		config: browserSync.Options;
	};
};

const configuration: CompilerOptionsConfiguration;

export default configuration;
