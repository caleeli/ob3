<script lang="ts">
	import { Button, ContentDialog } from 'fluent-svelte';
	import type GridConfig from 'src/lib/GridConfig';
	import type StoreInterface from 'src/lib/StoreInterface';
	import Form from './Form.svelte';
	import Grid from './Grid.svelte';

	export let config: GridConfig;
	export let store: StoreInterface;
	export let create: any[][];

	let open = false;
	let title = '';
	let record = {};
	let original: any | null = null;
	function add() {
		open = true;
		title = 'Add';
		record = {};
		original = null;
	}
	function edit(event: { detail: any }) {
		record = event.detail;
		original = JSON.parse(JSON.stringify(record));
		open = true;
		title = 'Edit';
	}
	async function deleteRecord(event: { detail: any }) {
		const record = event.detail;
		await store.delete(record.id);
		await store.refresh();
	}
	async function save() {
		open = false;
		if (original) {
			await store.update(original.id, record);
			await store.refresh();
		} else {
			await store.create(record);
			await store.refresh();
		}
	}
	function cancel() {
		open = false;
	}
</script>

<ContentDialog bind:open {title}>
	<Form content={create} border={false} data={record} />
	<svelte:fragment slot="footer">
		<Button variant="accent" on:click={save}>Save</Button>
		<Button on:click={cancel}>Cancel</Button>
	</svelte:fragment>
</ContentDialog>

<div class="toolbar">
	<Button variant="hyperlink" on:click={add}>
		<i class="icon icon-ic_fluent_add_16_regular" />
	</Button>
</div>

<Grid {config} {store} on:edit={edit} on:delete={deleteRecord} />

<style>
	.toolbar {
		display: flex;
		justify-content: space-between;
		align-items: center;
		padding: 0.5rem;
		background-color: var(--fds-solid-background-base);
	}
</style>
