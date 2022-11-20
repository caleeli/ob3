<script lang="ts">
	import { translation as __ } from '$lib/translations';
	import ApiStore from '$lib/ApiStore';
	import FileInput from '$lib/FileInput.svelte';
	import { Button, Checkbox, TextBox } from 'fluent-svelte';

	let subir_archivo: { filename: null };
	let name = '';
	let wireframes: {
		id: string;
		name: string;
		branch: string;
		selected: boolean;
	}[] = [];
</script>

<FileInput
	label="subir archivo"
	bind:value={subir_archivo}
	placeholder={__('archivo balsamic .bmpr')}
	store={new ApiStore({
		url: 'upload_balsamic',
	})}
	on:change={(e) => {
		name = ''; //e.detail.upload_name.substring(0, e.detail.upload_name.length - 5);
		wireframes = e.detail.wireframes;
	}}
/>

<h4>{__('Wireframes')}:</h4>
{#each wireframes as wireframe}
	<Checkbox value={wireframe.id} bind:checked={wireframe.selected}>
		{wireframe.name}
	</Checkbox>
	<br />
{/each}

<label for="name">{__('Name')}:</label>
<TextBox id="name" bind:value={name} placeholder={__('name')} />
<Button
	disabled={!subir_archivo || !name}
	on:click={async () => {
		const build = {
			attributes: {
				...subir_archivo,
				name,
				selected: wireframes
					.filter((wireframe) => wireframe.selected)
					.map((wireframe) => wireframe.id),
			},
		};
		await new ApiStore({
			url: 'process_balsamic',
		}).create(build);
		window.open(name, name);
	}}
>
	{__('Convert')}
</Button>
