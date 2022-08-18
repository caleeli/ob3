<script lang="ts">
	import { Button, ContentDialog, InfoBar } from 'fluent-svelte';
	import type GridConfig from '../lib/GridConfig';
	import type StoreInterface from '../lib/StoreInterface';
	import Form from './Form.svelte';
	import Grid from './Grid.svelte';
	import { translation as __ } from '../lib/translations';
	import type CrudAction from '$lib/CrudAction';

	export let config: GridConfig;
	export let store: StoreInterface;
	export let toolbar: CrudAction[];
	export let rowActions: CrudAction[];
	export let create: any[][];
	export let update: any[][];

	let form: any[][];
	let open = false;
	let confirmDelete = false;
	let title = '';
	let record = {};
	let original: any | null = null;
	let error = '';
	function add(initial = {}) {
		error = '';
		open = true;
		title = 'Add';
		record = initial;
		original = null;
	}
	function edit(event: { detail: any }) {
		error = '';
		record = event.detail;
		original = JSON.parse(JSON.stringify(record));
		open = true;
		title = 'Edit';
	}
	function deleteRecord(event: { detail: any }) {
		error = '';
		original = event.detail;
		confirmDelete = true;
	}
	async function confirmDeleteRecord() {
		try {
			await store.delete(original.id);
			await store.refresh();
			confirmDelete = false;
		} catch (err: any) {
			error = err.message;
		}
	}
	async function save() {
		try {
			if (original) {
				await store.update(original.id, record);
				await store.refresh();
			} else {
				await store.create(record);
				await store.refresh();
			}
			open = false;
		} catch (err: any) {
			error = err.message;
		}
	}
	function cancel() {
		open = false;
	}
	function doAction(tool: CrudAction) {
		if (tool.action === 'add') {
			form = tool.form;
			add(JSON.parse(JSON.stringify(tool.initial)));
		} else if (tool.action instanceof Function) {
			return tool.action();
		}
	}
	function doRowAction(action: string, event: { detail: any }) {
		const tool = rowActions.find((t) => t.action === action);
		if (action === 'edit') {
			form = (tool && tool.form) || [];
			edit(event);
		} else if (action === 'delete') {
			deleteRecord(event);
		}
	}
</script>

<ContentDialog bind:open title={__(title)} class="content-dialog-max-size">
	{#if error}
		<InfoBar message={error} severity="caution" />
	{/if}
	<Form content={form} border={false} data={record} />
	<svelte:fragment slot="footer">
		<Button variant="accent" on:click={save}>{__('Save')}</Button>
		<Button on:click={cancel}>{__('Cancel')}</Button>
	</svelte:fragment>
</ContentDialog>

<ContentDialog bind:open={confirmDelete} title={__('Delete')}>
	{#if error}
		<InfoBar message={error} severity="caution" />
	{/if}
	<p>{__('Are you sure to delete this record?')}</p>
	<svelte:fragment slot="footer">
		<Button variant="accent" on:click={confirmDeleteRecord}>{__('Delete')}</Button>
		<Button on:click={() => (confirmDelete = false)}>{__('Cancel')}</Button>
	</svelte:fragment>
</ContentDialog>

<div class="toolbar">
	{#each toolbar as tool}
		<Button variant="hyperlink" on:click={() => doAction(tool)}>
			<i class={`icon icon-ic_fluent_${tool.icon}_16_regular`} />
			{tool.label ? __(tool.label) : ''}
		</Button>
	{/each}
</div>

<Grid
	{config}
	{store}
	on:edit={(event) => doRowAction('edit', event)}
	on:delete={(event) => doRowAction('delete', event)}
/>

<style>
	.toolbar {
		display: flex;
		justify-content: space-between;
		align-items: center;
		padding: 0.5rem;
		background-color: var(--fds-solid-background-base);
	}
	:global(.content-dialog-max-size .content-dialog-body) {
		max-height: calc(100vh - 10rem);
		overflow: auto;
	}
</style>
