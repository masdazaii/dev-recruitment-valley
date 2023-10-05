import webpack from "webpack";
import path from "path";
import MiniCssExtractPlugin from "mini-css-extract-plugin";
import BrowserSyncPlugin from "browser-sync-webpack-plugin";
import { CleanWebpackPlugin } from "clean-webpack-plugin";
import ESLintWebpackPlugin from "eslint-webpack-plugin";
import CleanTerminalPlugin from "clean-terminal-webpack-plugin";
import _dotenv_ from "dotenv";
import config from "./compiler.options";
import autoprefixer from "autoprefixer";
import sass from "sass";

import jsconfig from "./jsconfig.json";

const { ProgressPlugin } = webpack;
const dotenv = _dotenv_.config({ path: path.join(__dirname, ".env") });

const aliasMapper = (callback = (alias) => alias) => {
  const aliases = {};
  Object.keys(jsconfig.compilerOptions.paths).forEach((key) => {
    const _key = key.replace("/*", "");
    const _val = jsconfig.compilerOptions.paths[key][0].replace("/*", "");
    aliases[_key] = callback(_val);
  });
  return aliases;
};

const alias = aliasMapper((alias) => path.resolve(__dirname, alias));

const entries = {};
const entriesRaw = [...config.javascript, ...config.sass.src].map((entry) => {
  if (typeof entry === "string") {
    const name = path.parse(path.basename(entry)).name;
    return {
      name,
      src: entry,
    };
  }

  return entry;
});

entriesRaw.forEach((item) => {
  const cssExt = /\.(css|sass|scss)$/;
  const jsExt = /\.(js)$/;
  const isCss = cssExt.test(item.src);
  const isJs = jsExt.test(item.src);
  const prefix = isCss ? "css/dist/" : isJs ? "js/dist/" : false;
  if (prefix) entries[`${prefix}${item.name}`] = item.src;
});

const configuration = (env, argv) => {
  return {
    entry: entries,
    mode: argv.mode === "production" ? "production" : "development",
    stats: "minimal",
    devtool: "source-map",
    watchOptions: {
      ignored: /node_modules/,
    },
    output: {
      path: path.resolve(__dirname, "./assets"),
      filename: (pathData) => {
        return pathData.chunk.name.indexOf("css/") !== -1
          ? "[name].__unused__.js"
          : "[name].min.js";
      },
    },
    resolve: {
      fallback: {
        fs: false,
      },

      // Please change you alias in the jsconfig.json file
      alias: alias,
    },
    performance: {
      hints: false,
    },
    ignoreWarnings: [
      {
        module: /handlebars/, // A RegExp
      },
    ],

    optimization: {
      splitChunks: {
        cacheGroups: {
          vendors: {
            test: /[\\/]node_modules[\\/]/,
            name: "js/dist/vendors",
            chunks: "all",
          },
        },
      },
    },

    module: {
      rules: [
        // JS RULES
        {
          test: /\.js$/,
          use: {
            loader: "babel-loader",
            options: {
              configFile: path.resolve(__dirname, ".babelrc"),
            },
          },
          exclude: {
            and: [/node_modules/],
            not: [
              // Match everything that has `firebase` in it, ex:
              // firebase,firebase/app, @firebase, @firebase/database
              /firebase/,
            ],
          },
        },

        // CSS rules
        {
          // sass / scss loader for webpack
          test: /\.(css|sass|scss)$/,
          use: [
            MiniCssExtractPlugin.loader,
            {
              loader: "css-loader",
              options: {
                sourceMap: true,
                url: false,
              },
            },
            {
              loader: "postcss-loader",
              options: {
                postcssOptions: {
                  plugins: [autoprefixer],
                },
              },
            },
            {
              loader: "sass-loader",
              options: {
                sourceMap: true,
                implementation: sass,
                sassOptions: {
                  outputStyle: "compressed",
                },
              },
            },
          ],
        },
      ],
    },
    plugins: [
      new CleanTerminalPlugin({
        beforeCompile: true,
        onlyInWatchMode: false,
      }),
      new ESLintWebpackPlugin({
        extensions: ["js", "mjs", "jsx", "ts", "tsx"],
        formatter: "visualstudio",
      }),
      new webpack.DefinePlugin({
        "process.env": dotenv.parsed,
      }),
      new MiniCssExtractPlugin({
        // define where to save the file
        filename: "[name].min.css",
      }),
      new BrowserSyncPlugin({
        ...config.browserSync.config,
        files: config.browserSync.watch,
        logLevel: "silent",
      }),
      new CleanWebpackPlugin({
        protectWebpackAssets: false,
        cleanOnceBeforeBuildPatterns: ["css/dist/**", "js/dist/**"],
        cleanAfterEveryBuildPatterns: ["**/*.__unused__.*"],
      }),
      new ProgressPlugin(),
    ],
  };
};

module.exports = configuration;
