<script lang="ts">
	import Header from '../stories/Header.svelte';
	import { access_token } from '../store';
	import Menu from '../lib/Menu.svelte';

	let user: { name: any; token: string } | null = null;
	access_token.subscribe((token) => {
		if (token) {
			user = {
				token: token,
				name: 'Harry'
			};
		}
	});
</script>

<Header {user} />

<main>
	{#if user}
		<Menu />
	{/if}
    <div class="content">
        <slot />
    </div>
</main>

<style>
	@import url('https://unpkg.com/fluent-svelte/theme.css');
	:global(body) {
		margin: 0;
		padding: 0;
		font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Oxygen', 'Ubuntu',
			'Cantarell', 'Fira Sans', 'Droid Sans', 'Helvetica Neue', sans-serif;
		-webkit-font-smoothing: antialiased;
		-moz-osx-font-smoothing: grayscale;
	}
	main {
		flex: 1;
		display: flex;
		flex-direction: row;
		padding: 0;
		width: 100%;
		/*max-width: 1024px;*/
		margin: 0 auto;
		box-sizing: border-box;
	}

	@media (min-width: 480px) {
		footer {
			padding: 40px 0;
		}
	}

    .content {
		flex-grow: 1;
        padding: 1rem;
	}
</style>
