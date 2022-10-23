<script lang="ts">
	import { Button, ContentDialog } from 'fluent-svelte';
	import Form from '../stories/Form.svelte';
	import { translation as __ } from '../lib/translations';
	import type FormField from './FormField';
	import { createEventDispatcher } from 'svelte';

	const dispatch = createEventDispatcher();

	export let form: FormField[][];
	export let open = false;
	export let data: any = {};
	export let title = '';

	let error = '';
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
