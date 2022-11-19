<script lang="ts">
	import 'fluent-svelte/theme.css';
	import Header from '../stories/Header.svelte';
	import { login, edit_mode } from '../store';
	import Menu from '../lib/Menu.svelte';
	import { page } from '$app/stores';
	import { goto } from '$app/navigation';

	let isLogin = $page.url.pathname === '/';
	let user: { name: any } | null = null;
	let shiftKey: boolean = false;
	let ctrlKey: boolean = false;
	login.subscribe((login: { attributes: { name: string } } | null): void => {
		if (login) {
			user = {
				name: login.attributes.name,
			};
		}
	});

	function handleKeydown(event: { keyCode: number; shiftKey: any; ctrlKey: any }) {
		// F2: Change to edit mode
		if (event.keyCode === 113) {
			edit_mode.update((value) => !value);
		}
		shiftKey = event.shiftKey;
		ctrlKey = event.ctrlKey;
	}
	function handleKeyup(event: { keyCode: number; shiftKey: any; ctrlKey: any }) {
		shiftKey = event.shiftKey;
		ctrlKey = event.ctrlKey;
	}
	function logout() {
		login.set(null);
		goto('/');
	}
	$: isLogin = $page.url.pathname === '/';
</script>

<svelte:window on:keydown={handleKeydown} on:keyup={handleKeyup} />

<Header {user} on:logout={logout} {isLogin} />

<main class={`${shiftKey ? 'shift-pressed' : ''} ${ctrlKey ? 'control-pressed' : ''}`}>
	{#if user && !isLogin}
		<Menu />
	{/if}
	<div class="content">
		<slot />
	</div>
</main>

<style>
	/* @import url('https://unpkg.com/fluent-svelte/theme.css'); */
	@import url('../lib/theme.css');
	:root {
		--header-height: 6rem;
	}
	:global(body) {
		margin: 0;
		padding: 0;
		overflow: hidden;
		width: 100vw;
		height: 100vh;
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
		margin: 0 auto;
		box-sizing: border-box;
		height: calc(100vh - var(--header-height));
		overflow: hidden;
	}
	.content {
		flex-grow: 1;
		width: 100rem;
		overflow: auto;
		height: 100%;
	}
</style>
