import adapter from '@sveltejs/adapter-auto';
import preprocess from 'svelte-preprocess';
import autoprefixer from 'autoprefixer';
import sass from 'sass';

/** @type {import('@sveltejs/kit').Config} */
const config = {
	// Consult https://github.com/sveltejs/svelte-preprocess
	// for more information about preprocessors
	preprocess: preprocess({
		postcss: {
			plugins: [
				autoprefixer
			]
		  },
		  sass: {
			sync: true,
			implementation: sass,
		  },
	}),

	kit: {
		adapter: adapter()
	}
};

export default config;
