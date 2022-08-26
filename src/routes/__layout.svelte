<script lang="ts">
	import Header from '../stories/Header.svelte';
	import { login } from '../store';
	import Menu from '../lib/Menu.svelte';

	let user: { name: any } | null = null;
	login.subscribe((login: { attributes: { name: string } } | null): void => {
		if (login) {
			user = {
				name: login.attributes.name
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
		height: calc(100vh - 4rem);
		overflow: hidden;
	}

	@media (min-width: 480px) {
		footer {
			padding: 40px 0;
		}
	}

	.content {
		flex-grow: 1;
		width: 100rem;
		overflow: auto;
		height: 100%;
	}
</style>
