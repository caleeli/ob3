<script lang="ts">
	import type CrudAction from '../lib/CrudAction';
	import ApiStore, {
		JSONApiPageHandler,
		JSONApiPerPageHandler,
		JSONApiSortHandler,
	} from '../lib/ApiStore';
	import type GridConfig from '../lib/GridConfig';
	import Crud from '../stories/CRUD.svelte';
	import headers from './auditoriaListaOperaciones.json';

	let config: GridConfig = {
		multiSelect: true,
		headers: headers,
		sort: [],
	};
	let store = new ApiStore({
		url: 'operaciones',
		root: 'data',
		limit: 200,
		query: {
			per_page: JSONApiPerPageHandler,
			page: JSONApiPageHandler,
			sort: JSONApiSortHandler,
		},
	});
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
			},
		},
	];
</script>

<Crud {config} {store} {toolbar} configStoreId="auditoriaListaOperaciones" />
