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
	function add() {
		open = true;
		title = 'Add';
	}
	function edit(event: { detail: any }) {
		record = event.detail;
		open = true;
		title = 'Edit';
	}
	function save() {
		open = false;
		store.save(record);
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

<Grid {config} {store} on:edit={edit} />

<style>
	.toolbar {
		display: flex;
		justify-content: space-between;
		align-items: center;
		padding: 0.5rem;
		background-color: var(--fds-solid-background-base);
	}
</style>
