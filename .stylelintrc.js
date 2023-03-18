module.exports = {
	extends: ['@dekode/stylelint-config'],
	rules: {
		'at-rule-no-unknown': [
			true,
			{
				ignoreAtRules: ['mixin', 'define-mixin'],
			},
		],
	},
};
