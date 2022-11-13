<script lang="ts">
	import { translation as __ } from '$lib/translations';
	import ApiStore from '$lib/ApiStore';
	import FileInput from '$lib/FileInput.svelte';
	import { Button } from 'fluent-svelte';

	let subir_archivo: { filename: null };
</script>

<FileInput
	label="subir archivo"
	bind:value={subir_archivo}
	placeholder={__('archivo balsamic .bmpr')}
	store={new ApiStore({
		url: 'upload_balsamic',
	})}
/>

<Button
	disabled={!subir_archivo}
	on:click={() => {
		new ApiStore({
			url: 'process_balsamic',
		}).create({
			attributes: subir_archivo,
		});
	}}
>
	{__('Convert')}
</Button>
