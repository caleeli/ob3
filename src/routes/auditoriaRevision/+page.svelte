<script lang="ts">
	import type CrudAction from '$lib/CrudAction';
	import ApiStore, {
		JSONApiPageHandler,
		JSONApiPerPageHandler,
		JSONApiSortHandler,
	} from '$lib/ApiStore';
	import type GridConfig from '$lib/GridConfig';
	import Crud from '../../stories/CRUD.svelte';
	import page_config from './auditoriaRevision.json';
	import ConfigStore from '$lib/ConfigStore';
	import { page } from '$app/stores';
	import type FormField from '$lib/FormField';
	import { feel } from '$lib/helpers';

	let configStore = new ConfigStore('auditoriaRevision', page_config);
	let config: GridConfig = page_config.grid;
	let store = new ApiStore(
		Object.assign(page_config.store, {
			root: 'data',
			query: {
				per_page: JSONApiPerPageHandler,
				page: JSONApiPageHandler,
				sort: JSONApiSortHandler,
				params: {
					informe_id: $page.url.searchParams.get('inf'),
					prmprnpre_num: $page.url.searchParams.get('pre'),
					tipo_credito: $page.url.searchParams.get('tc'),
					calidad: $page.url.searchParams.get('cal'),
				},
			},
		})
	);
	let toolbar: CrudAction[] = [];
	let rowActions: CrudAction[] = page_config.rowActions || [];
	let form: FormField[][] = page_config.form || [];
	let formStore = new ApiStore(
		Object.assign(page_config.formStore, {
			root: 'data',
		})
	);
	let data = {};
	formStore
		.show(feel(page_config.formStore.currentId, $page), {})
		.then((response) => {
			data = response;
		})
		.catch((error) => {
			console.error(error);
		});
</script>

<Crud {config} {store} {toolbar} {configStore} {rowActions} {form} {data} {formStore} />
