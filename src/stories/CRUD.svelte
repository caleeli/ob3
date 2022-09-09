<script lang="ts">
	import { Button, ContentDialog, InfoBar } from 'fluent-svelte';
	import type GridConfig from '../lib/GridConfig';
	import type StoreInterface from '../lib/StoreInterface';
	import Form from './Form.svelte';
	import Grid from './Grid.svelte';
	import { translation as __ } from '../lib/translations';
	import type CrudAction from '../lib/CrudAction';
	import type ConfigStore from '../lib/ConfigStore';
	import type FormField from '../lib/FormField';

	export let config: GridConfig;
	export let store: StoreInterface;
	export let toolbar: CrudAction[] = [];
	export let rowActions: CrudAction[] = [];
	export let configStore: ConfigStore | undefined;
	export let form: FormField[][] = [];
	export let data: any = null;

	let formPopup: any[][];
	let open = false;
	let confirmDelete = false;
	let title = '';
	let record = {};
	let original: any | null = null;
	let error = '',
		error_suffix = '';
	let selected: number[] = [];
	let handler: ((record: any, selected: any[]) => Promise<void>) | undefined;
	function add(initial = {}, popupTitle = 'Add') {
		error = '';
		open = true;
		title = popupTitle;
		record = initial;
		original = null;
	}
	function edit(editRecord: any) {
		error = '';
		record = editRecord;
		original = JSON.parse(JSON.stringify(record));
		open = true;
		title = 'Edit';
	}
	function deleteRecord(record: any) {
		error = '';
		if (!record || (record instanceof Array && !record.length)) {
			alertError('Please select a record to delete');
			return;
		}
		original = record;
		confirmDelete = true;
	}
	async function confirmDeleteRecord() {
		try {
			if (original instanceof Array) {
				original.forEach(async (record) => {
					await store.delete(record.id);
					store.refresh();
				});
			} else {
				await store.delete(original.id);
				await store.refresh();
			}
			confirmDelete = false;
		} catch (err: any) {
			alertError(err.message);
		}
	}
	async function save() {
		try {
			if (handler) {
				await handler(record, selected);
				return;
			}
			if (original) {
				await store.update(original.id, record);
				await store.refresh();
			} else {
				await store.create(record);
				await store.refresh();
			}
			open = false;
		} catch (err: any) {
			alertError(err.message);
		}
	}
	function alertError(message: string) {
		// error_suffix to refresh the form after an error
		error_suffix = error_suffix ? '' : ' ';
		error = __(message) + error_suffix;
	}
	function cancel() {
		open = false;
	}
	function doAction(payload: { [key: string]: CrudAction | any }) {
		const tool = payload.tool;
		if (tool.form && tool.initial) {
			formPopup = tool.form;
			add(JSON.parse(JSON.stringify(tool.initial)));
		} else if (tool.form) {
			formPopup = tool.form;
			handler = tool.handler;
			add({}, tool.label);
		} else if (tool.action instanceof Function) {
			return tool.action();
		} else if (typeof tool.action === 'string') {
			const context = Object.assign({}, payload, {
				deleteRecord(record: any) {
					deleteRecord({ detail: record });
				},
			});
			const input = Object.keys(context);
			const fn = new Function(...input, 'return ' + tool.action);
			return fn(...input.map((key) => context[key]));
		}
	}
	function doRowAction(action: string, event: { detail: any }) {
		const tool = event.detail.tool;
		if (action === 'edit') {
			formPopup = (tool && tool.form) || [];
			edit(event.detail.selected);
		} else if (action === 'delete') {
			deleteRecord(event.detail.selected);
		} else if (action === 'select') {
			formPopup = (tool && tool.form) || [];
			edit(event.detail.selected);
		}
	}
</script>

<ContentDialog bind:open title={__(title)} class="content-dialog-max-size">
	<Form content={formPopup} border={false} data={record} {error} {configStore} />
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

{#if error}
	<InfoBar message={error} severity="caution" />
{/if}

{#if form && form.length}
	<Form content={form} border={false} {data} {configStore} />
{/if}

<Grid
	{config}
	{store}
	{configStore}
	{toolbar}
	{rowActions}
	on:edit={(event) => doRowAction('edit', event)}
	on:delete={(event) => doRowAction('delete', event)}
	on:select={(event) => doRowAction('select', event)}
	on:toolbar={(event) => doAction(event.detail)}
	bind:selected
/>

<style>
	:global(.content-dialog-max-size) {
		overflow: visible !important;
	}
	:global(.content-dialog-max-size .content-dialog-body) {
		max-height: calc(100vh - 10rem);
	}
</style>
