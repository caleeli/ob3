<script lang="ts">
	import type CrudAction from '$lib/CrudAction';
	import ApiStore, {
		JSONApiPageHandler,
		JSONApiPerPageHandler,
		JSONApiSortHandler,
	} from '$lib/ApiStore';
	import type GridConfig from '$lib/GridConfig';
	import Crud from '../../stories/CRUD.svelte';
	import page_config from './auditoriaListaOperaciones.json';
	import ConfigStore from '$lib/ConfigStore';

	let configStore = new ConfigStore('auditoriaListaOperaciones', page_config);
	let config: GridConfig = page_config.grid;
	let store = new ApiStore(
		Object.assign(page_config.store, {
			root: 'data',
			limit: 200,
			query: {
				per_page: JSONApiPerPageHandler,
				page: JSONApiPageHandler,
				sort: JSONApiSortHandler,
			},
		})
	);
	let toolbar: CrudAction[] = [
		{
			icon: 'add',
			label: 'Agregar a Muestra',
			form: [
				[
					{
						control: 'TextBox',
						type: 'text',
						name: 'numero',
						label: 'Nro Informe',
					},
				],
				[
					{
						control: 'ComboBox',
						name: 'revisor',
						label: 'Revisor',
						storeValueField: 'id',
						storeNameField: 'attributes.name',
						store: new ApiStore({
							url: 'users',
							root: 'data',
							query: {
								filter: (store: ApiStore) => [`filterByName(${JSON.stringify(store.searchValue)})`],
							},
						}),
					},
				],
				[
					{
						control: 'ComboBox',
						name: 'sucursal',
						label: 'Sucursal',
						storeValueField: 'id',
						storeNameField: 'attributes.name',
						store: new ApiStore({
							url: 'sucursales',
							root: 'data',
							query: {
								filter: (store: ApiStore) => [`filterByName(${JSON.stringify(store.searchValue)})`],
							},
						}),
					},
				],
			],
			async handler(record: any, selected: any[]) {
				console.log(record, selected);
				const api = new ApiStore({url: 'muestra'});
				const res = await api.get();
				console.log(res);
			},
		},
	];
</script>

<Crud {config} {store} {toolbar} {configStore} />
