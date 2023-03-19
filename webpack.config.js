/* eslint-disable import/no-extraneous-dependencies, @wordpress/dependency-group */

/**
 * External dependencies
 */
const { sync: globSync } = require('fast-glob');
const MiniCSSExtractPlugin = require('mini-css-extract-plugin');
const path = require('path');
const RemoveEmptyScriptsPlugin = require('webpack-remove-empty-scripts');
const webpack = require('webpack');

/**
 * WordPress dependencies
 */
const DependencyExtractionWebpackPlugin = require('@wordpress/dependency-extraction-webpack-plugin');
const defaultConfig = require('./node_modules/@wordpress/scripts/config/webpack.config');

/**
 * Patches config to use resolve-url-loader for relative paths in SCSS files
 * It will be possible to use './images/png.png' inside the SCSS,
 *
 * All paths will be treated as relative to file, not project root.
 */
for (const rule of defaultConfig.module.rules) {
	if ('any-filename-to-test.scss'.match(rule.test)) {
		const sassLoaderConfig = rule.use.pop();

		// We mutate default config to change behaviour.
		rule.use = [
			...rule.use,
			// resolve-url-loader should be executed right after sass-loader.
			{
				loader: require.resolve('resolve-url-loader'),
				options: {
					sourceMap: sassLoaderConfig.options.sourceMap,
					removeCR: true,
				},
			},
			{
				...sassLoaderConfig,
				options: {
					...sassLoaderConfig.options,
					sourceMap: true, // It's required to have sourceMap for resolve-url-loader, resolve-url-loader will keep or drop maps on it's step.
				},
			},
		];
	}
}

/**
 * Collect information about all packages
 * and their entry files using entry-files.json files.
 *
 * @return {Object[]} Array of items with information
 */
function getEntryFiles() {
	const entries = [];

	// Only include directories that contains a entry-files.json file.
	const files = globSync('./entry-files.json', { onlyFiles: true });

	files.forEach((file) => {
		const entryFiles = require(file); // eslint-disable-line

		entries.push({
			dir: path.dirname(file),
			files: entryFiles.map((entryFile) => `src/${entryFile}`),
		});
	});

	return entries;
}

/**
 * Prepares Config for defined package and entry files,
 *
 * Future: We can differ configuration between packages or use their own configuration
 * or implement importing webpack.config.js from their folders.
 *
 * @param {string} dir   Package full path.
 * @param {Array}  files Relative paths of enries.
 * @return {webpack.Configuration} Return single webpack configuration.
 */
const prepareConfig = (dir, files) => {
	const entries = {};

	files.forEach((file) => {
		const filePath = path.resolve(__dirname, dir, file);
		const pathParts = path.parse(file);
		const fileName = pathParts.name;
		let subDirectory = pathParts.dir;

		/**
		 * Remove src folder from path to add support nested directories in entry-files.json
		 * Like: ["index.js", "hero/index.js", "style.css"]
		 * All assets of hero/index.js should be places to build/hero/
		 */
		subDirectory = subDirectory
			.split('/')
			.filter((folder, i) => !(folder === 'src' && i === 0))
			.join('/');

		const entryKey = (subDirectory ? subDirectory + '/' : '') + fileName;

		if (typeof entries[entryKey] === 'undefined') {
			entries[entryKey] = [];
		}

		entries[entryKey].push(filePath);
	});

	const config = {
		...defaultConfig,

		name: dir,
		entry: entries,

		output: {
			path: path.resolve(__dirname, dir, 'build'),
			filename: '[name].js',
		},
		optimization: {
			...defaultConfig.optimization,
			removeEmptyChunks: true,

			splitChunks: {
				cacheGroups: {
					internalStyle: {
						type: 'css/mini-extract',
						test: /[\\/]+?\.(sc|sa|c)ss$/,
						chunks: 'all',
						enforce: true,
					},
					default: false,
				},
			},
		},

		resolve: {
			...defaultConfig.resolve,
			alias: {
				...defaultConfig.resolve.alias,
				components: path.resolve(__dirname, 'packages', 'components'),
			},
		},

		// Hides rarely used information for more compact appearance of console.
		stats: {
			children: false,
			all: false,
			entrypoints: true,
			warnings: true,
			errors: true,
			hash: false,
			timings: true,
			errorDetails: true,
			builtAt: true,
		},

		plugins: [
			new MiniCSSExtractPlugin({ filename: '[name].css' }),

			/**
			 * It removes empty JS files, when we use CSS/SCSS as main entrypoint of asset.
			 *
			 * It's possible to remove also `.asset.php` by writing custom version
			 */
			new RemoveEmptyScriptsPlugin(),

			new DependencyExtractionWebpackPlugin({ injectPolyfill: true }),

			(process.argv || []).includes('--progress') &&
				new webpack.ProgressPlugin(),
		].filter(Boolean),
	};

	return config;
};

const files = getEntryFiles();

/**
 * We can use Multi-Config mode to build packages in 'sandboxed' mode and parallel.
 *
 * https://webpack.js.org/configuration/configuration-types/#exporting-multiple-configurations
 */
const buildConfig = files.map((item) => prepareConfig(item.dir, item.files));

/**
 * Add settings for development mode.
 */
buildConfig.push({
	...defaultConfig,

	entry: {
		settings: ['./src/settings/settings.js', './src/settings/settings.css'],
	},

	output: {
		path: path.resolve(__dirname, 'build/settings'),
		filename: '[name].js',
	},

	externals: {
		react: 'React',
		'react-dom': 'ReactDOM',
	},

	optimization: {
		...defaultConfig.optimization,
		removeEmptyChunks: true,

		splitChunks: {
			cacheGroups: {
				internalStyle: {
					type: 'css/mini-extract',
					test: /[\\/]+?\.(sc|sa|c)ss$/,
					chunks: 'all',
					enforce: true,
				},
				default: false,
			},
		},
	},

	resolve: {
		...defaultConfig.resolve,
		alias: {
			...defaultConfig.resolve.alias,
			components: path.resolve(__dirname, 'packages', 'components'),
		},
	},

	// Hides rarely used information for more compact appearance of console.
	stats: {
		children: false,
		all: false,
		entrypoints: true,
		warnings: true,
		errors: true,
		hash: false,
		timings: true,
		errorDetails: true,
		builtAt: true,
	},

	plugins: [
		new MiniCSSExtractPlugin({ filename: '[name].css' }),
		/**
		 * It removes empty JS files, when we use CSS/SCSS as main entrypoint of asset.
		 *
		 * It's possible to remove also `.asset.php` by writing custom version
		 */
		new RemoveEmptyScriptsPlugin(),

		new DependencyExtractionWebpackPlugin({ injectPolyfill: true }),
	],
});

module.exports = buildConfig;
