<script lang="ts">
	import ApiStore from '$lib/ApiStore';
	import ConfigStore from '$lib/ConfigStore';
	import FileInput from '$lib/FileInput.svelte';
	import Grid from '$lib/Grid.svelte';
	import GridConfig from '$lib/GridConfig';
	import config from './config.json';
	import fileStore from '$lib/FileStore';
	import { Button } from 'fluent-svelte';
	import { TextBox } from 'fluent-svelte';
	import { translation as __ } from '$lib/translations';

	let subir_archivo: { filename: null };
	let buscar: string;
	let configStore = new ConfigStore('test', config);
</script>

<!--71EDA1FC-766D-99F9-91B9-E81A8B19B650 - Master-->
<div class="row">
	<div class="cell"><h2>{__('Asignación de Tareas')}</h2></div>
	<div class="cell" />
</div>
<div class="row">
	<div class="cell">
		<FileInput bind:value={subir_archivo} placeholder={__('subir archivo')} store={fileStore} />
		<Button>Guardar</Button>
	</div>
	<div class="cell" />
</div>
<div class="row">
	<div class="cell">
		<TextBox bind:value={buscar} placeholder={__('buscar')} />
		<Button>Buscar</Button>
	</div>
</div>
<div class="row">
	<div class="cell">
		<Grid
			config={new GridConfig(config.grid_10)}
			store={new ApiStore(config.gridStore_10)}
			{configStore}
		/>
	</div>
</div>
