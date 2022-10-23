<script lang="ts">
	import './page.css';
	import Header from './Header.svelte';

	let user: { name: any } | null | undefined = null;
</script>

<Header
	{user}
	on:login={() => (user = { name: 'Jane Doe' })}
	on:logout={() => (user = null)}
	on:createAccount={() => (user = { name: 'Jane Doe' })}
/>

{#if user}
	<p>Hello, {user.name}</p>
{/if}
