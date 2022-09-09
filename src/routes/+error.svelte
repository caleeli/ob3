<script lang="ts">
	import ConfigStore from '$lib/ConfigStore';

	import { Button } from 'fluent-svelte';
	import { translation as __ } from '../lib/translations';
	import { page } from '$app/stores';

	function createCRUDPage() {
		const page_name = $page.url.pathname.split('/', 2)[1];
		const store = new ConfigStore(page_name, {
			grid: {
				multiSelect: false,
				headers: [
					{
						label: 'ID',
						value: 'id',
						field: 'id',
						align: 'left',
					},
				],
				sort: [],
			},
			store: {
				url: 'operaciones',
				root: 'data',
				limit: 100,
				query: [],
			},
		});
		store.create();
	}
</script>

<main>
	<h1>{$page.status}: {($page.error && $page.error.message) || ''}</h1>

	{#if $page.status == 404}
		<Button on:click={createCRUDPage}
			>{__('Create a CRUD page')} for '{$page.url.pathname.split('/', 2)[1]}'</Button
		>
	{/if}
</main>
