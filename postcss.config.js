/**
 * External dependencies
 */
const atImport = require('postcss-import');
const autoprefixer = require('autoprefixer');
const cssnano = require('cssnano');
const customMedia = require('postcss-custom-media');
const minmax = require('postcss-media-minmax');
const mixins = require('postcss-mixins');
const nested = require('postcss-nested');
const postcssFlexbugsFixes = require('postcss-flexbugs-fixes');

module.exports = (ctx) => {
	const config = {
		plugins: [
			atImport(),
			mixins,
			postcssFlexbugsFixes,
			nested,
			customMedia(),
			minmax(),
			autoprefixer,
		],
	};

	if (ctx.env === 'production') {
		config.plugins.push(cssnano());
	}

	return config;
};
