<script lang="ts">
	import type CrudAction from '$lib/CrudAction';
	import ApiStore, {
		JSONApiPageHandler,
		JSONApiPerPageHandler,
		JSONApiSortHandler,
	} from '$lib/ApiStore';
	import type GridConfig from '$lib/GridConfig';
	import Crud from '../../stories/CRUD.svelte';
	import page_config from './auditoriaRevisionListaMuestra.json';
	import ConfigStore from '$lib/ConfigStore';

	let configStore = new ConfigStore('auditoriaRevisionListaMuestra', page_config);
	let config: GridConfig = page_config.grid;
	let store = new ApiStore(
		Object.assign(page_config.store, {
			root: 'data',
			query: {
				per_page: JSONApiPerPageHandler,
				page: JSONApiPageHandler,
				sort: JSONApiSortHandler,
			},
		})
	);
	let toolbar: CrudAction[] = page_config.toolbar;
</script>

<Crud {config} {store} {toolbar} {configStore} />
