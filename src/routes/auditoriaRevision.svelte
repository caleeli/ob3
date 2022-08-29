<script lang="ts">
	import type CrudAction from '../lib/CrudAction';
	import ApiStore, {
		JSONApiPageHandler,
		JSONApiPerPageHandler,
		JSONApiSortHandler,
	} from '../lib/ApiStore';
	import type GridConfig from '../lib/GridConfig';
	import Crud from '../stories/CRUD.svelte';
	import page_config from './auditoriaRevision.json';
	import ConfigStore from '../lib/ConfigStore';
	import { page } from '$app/stores';

	let configStore = new ConfigStore('auditoriaRevision', page_config);
	let config: GridConfig = page_config.grid;
	let store = new ApiStore(
		Object.assign(page_config.store, {
			root: 'data',
			query: {
				per_page: JSONApiPerPageHandler,
				page: JSONApiPageHandler,
				sort: JSONApiSortHandler,
				//filter: [`informe(${$page.url.searchParams.get('inf')},${$page.url.searchParams.get('pre')},${$page.url.searchParams.get('tc')})`],
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
</script>

<Crud {config} {store} {toolbar} {configStore} />
