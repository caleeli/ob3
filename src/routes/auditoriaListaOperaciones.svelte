<script lang="ts">
	import type CrudAction from '$lib/CrudAction';
	import ApiStore, {
		JSONApiPageHandler,
		JSONApiPerPageHandler,
		JSONApiSortHandler,
	} from '../lib/ApiStore';
	import type GridConfig from '../lib/GridConfig';
	import Crud from '../stories/CRUD.svelte';

	let config: GridConfig = {
		multiSelect: true,
		headers: [
			{
				label: 'ID',
				value: 'id',
				field: 'id',
				align: 'left',
			},
			{
				label: 'Cod Préstamo',
				value: 'attributes.prmprnpre',
				field: 'prmprnpre',
				align: 'left',
			},
			{
				label: 'Asesor',
				value: 'attributes.asesor',
				field: 'asesor',
				align: 'left',
			},
			{
				label: 'Producto',
				value: 'attributes.prtcrdesc',
				field: 'prtcrdesc',
				align: 'left',
			},
			{
				label: 'Monto Desemb',
				value: 'attributes.prmprmdes',
				field: 'prmprmdes',
				align: 'right',
				format: ['currency'],
			},
			{
				label: 'Saldo',
				value: 'attributes.prmprsald',
				field: 'prmprsald',
				align: 'right',
				format: ['currency'],
			},
			{
				label: 'Moneda',
				value: 'attributes.moneda',
				field: 'moneda',
				align: 'left',
			},
			{
				label: 'Fecha Desemb',
				value: 'attributes.prmprfdes',
				field: 'prmprfdes',
				align: 'left',
			},
			{
				label: 'Ini Plan',
				value: 'attributes.ini_plan_pago',
				field: 'ini_plan_pago',
				align: 'left',
			},
			{
				label: 'Ult Pago',
				value: 'attributes.ult_pago',
				field: 'ult_pago',
				align: 'left',
			},
			{
				label: 'Incumplimiento',
				value: 'attributes.fec_incumplimiento',
				field: 'fec_incumplimiento',
				align: 'left',
			},
			{
				label: 'Cierre',
				value: 'attributes.fec_cierre',
				field: 'fec_cierre',
				align: 'left',
			},
			{
				label: 'Nro Cuotas',
				value: 'attributes.num_cuotas',
				field: 'num_cuotas',
				align: 'left',
			},
			{
				label: 'Plazo',
				value: 'attributes.prmprplaz',
				field: 'prmprplaz',
				align: 'left',
			},
			{
				label: 'Mora',
				value: 'attributes.mora',
				field: 'mora',
				align: 'left',
			},
			{
				label: 'Estado',
				value: 'attributes.estado',
				field: 'estado',
				align: 'left',
			},
			{
				label: 'Tasa de interes',
				value: 'attributes.tasa',
				field: 'tasa',
				align: 'right',
			},
			{
				label: 'Form',
				value: 'attributes.prmprfpvc',
				field: 'prmprfpvc',
				align: 'left',
			},
			{
				label: 'Gasto',
				value: 'attributes.gasto',
				field: 'gasto',
				align: 'right',
				format: ['currency'],
			},
		],
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
						options: [],
						name: 'sucursal',
						label: 'Sucursal',
					},
				],
			],
			async handler(record: any, selected: any[]) {
				console.log(record, selected);
			},
		},
	];
</script>

<Crud {config} {store} {toolbar} />
