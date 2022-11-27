<script lang="ts">
	import { translation as __ } from '$lib/translations';
	import ApiStore from '$lib/ApiStore';
	import FileInput from '$lib/FileInput.svelte';
	import { Button, Checkbox, TextBox } from 'fluent-svelte';
	const backend_base = import.meta.env.VITE_BACKEND_URL;

	let subir_archivo: { filename: null };
	let fileID = '';
	let name = '';
	let wireframes: {
		id: string;
		name: string;
		branch: string;
		selected: boolean;
	}[] = [];
	const gdrive_auth = backend_base + 'gdrive_auth';
	let token = '';
	// get gdrive token from local storage when not SSR
	if (typeof window !== 'undefined') {
		token = localStorage.getItem('gdrive_token') || '';
	}
	// save gdrive token to local storage
	function saveToken() {
		localStorage.setItem('gdrive_token', token);
	}
</script>

<p>
	<Button
		variant="accent"
		on:click={async () => {
			window.open(gdrive_auth, 'gdrive_auth');
			// listen post message from gdrive auth window
			window.addEventListener('message', (event) => {
				if (!event.origin || event.origin !== backend_base.substring(0, event.origin.length)) {
					console.log(event.origin);
					return;
				}
				console.log('saved!');
				token = event.data;
				saveToken();
			});
		}}
	>
		{__('Conectar a Google Drive')}
	</Button>
	{token ? 'Conectado' : 'Desconectado'}
</p>

<p>
	<label for="fileID">{__('GDrive FileID o Link compartido')}:</label>
	<TextBox id="fileID" bind:value={fileID} placeholder={__('GDrive FileID o Link compartido')} />
</p>
<p>
	<Button
		disabled={!fileID && !token}
		on:click={async () => {
			// check if fileID is a URL
			const isURL = fileID.match(/https:\/\/drive.google.com\/file\/d\/(.*)\/view/);
			if (isURL) {
				fileID = isURL[1];
			}
			if (!token) {
				window.open(gdrive_auth, 'gdrive_auth');
			}
			const build = {
				attributes: {
					fileID,
					token,
				},
			};
			let file = await new ApiStore({
				url: 'gdrive_file',
			}).create(build);
			subir_archivo = file.attributes;
			wireframes = file.attributes.wireframes;
			console.log(subir_archivo);
		}}
	>
		{__('Cargar desde GDrive')}
	</Button>
</p>

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
