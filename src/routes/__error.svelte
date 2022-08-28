<script context="module" lang="ts">
	export function load(data: { status: string; error: { message: string } }) {
		return {
			props: {
				status: data.status,
				error: data.error.message,
			},
		};
	}
</script>

<script lang="ts">
	import ConfigStore from '$lib/ConfigStore';

	import { Button } from 'fluent-svelte';
	import { translation as __ } from '../lib/translations';

	export let status: string;
	export let error: string;

	function createCRUDPage() {
		const page_name = error.split('/', 2)[1];
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
	<h1>{status}: {error}</h1>

	{#if status == '404'}
		<Button on:click={createCRUDPage}>{__('Create a CRUD page')}</Button>
	{/if}
</main>
