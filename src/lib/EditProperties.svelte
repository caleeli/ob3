<script lang="ts">
	import { Button, ContentDialog, InfoBar } from 'fluent-svelte';
	import type StoreInterface from '../lib/StoreInterface';
	import Form from '../stories/Form.svelte';
	import { translation as __ } from '../lib/translations';
	import type FormField from './FormField';
	import { createEventDispatcher } from 'svelte';

	const dispatch = createEventDispatcher();

	let store: StoreInterface;
	export let form: FormField[][];
	export let open = false;
	export let data: any = {};

	let title = '';
	let original: any | null = null;
	let error = '',
		error_suffix = '';
	let selected: number[] = [];
	let handler: ((record: any, selected: any[]) => Promise<void>) | undefined;
	async function save() {
		dispatch('save', data);
		open = false;
	}
	function cancel() {
		open = false;
	}
</script>

<ContentDialog bind:open title={__(title)} class="content-dialog-max-size">
	<Form content={form} border={false} {data} {error} />
	<svelte:fragment slot="footer">
		<Button variant="accent" on:click={save}>{__('Save')}</Button>
		<Button on:click={cancel}>{__('Cancel')}</Button>
	</svelte:fragment>
</ContentDialog>
