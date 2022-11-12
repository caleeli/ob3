const istanbul = require('vite-plugin-istanbul');
const constants = require('@storybook/addon-coverage/dist/cjs/constants');
const { mergeConfig } = require('vite');

module.exports = {
	stories: ['../src/**/*.stories.mdx', '../src/**/*.stories.@(js|jsx|ts|tsx|svelte)'],
	addons: [
		'@storybook/addon-links',
		'@storybook/addon-essentials',
		'@storybook/addon-interactions',
		{
			name: '@storybook/addon-coverage',
			options: {
				istanbul: {
					include: [
						'src/**/*.svelte',
						'src/**/*.js',
						'src/**/*.ts',
						"**/stories/**"
					]
				}
			}
		}
	],
	framework: '@storybook/svelte',
	core: {
		builder: '@storybook/builder-vite',
	},
	svelteOptions: {
		preprocess: import('../svelte.config.js'),
	},
	features: {
		storyStoreV7: true,
	},
	async viteFinal(config) {
		return mergeConfig(config, {
			// customize the Vite config here
			build: {
				sourcemap: true
			},
			plugins: [
				istanbul({
					exclude: constants.defaultExclude,
					extension: constants.defaultExtensions
				})
			]
		});
	},
};
