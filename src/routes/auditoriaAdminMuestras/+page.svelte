<script lang="ts">
	import type CrudAction from '$lib/CrudAction';
	import ApiStore, {
		JSONApiPageHandler,
		JSONApiPerPageHandler,
		JSONApiSortHandler,
	} from '$lib/ApiStore';
	import type GridConfig from '$lib/GridConfig';
	import Crud from '../../stories/CRUD.svelte';

	let config: GridConfig = {
		multiSelect: false,
		headers: [
			{
				label: 'Informe',
				value: 'attributes.informe',
				field: 'informe',
				align: 'left',
				groupRows: true,
			},
			{
				label: 'Calidad',
				value: 'attributes.calidad',
				field: 'calidad',
				align: 'left',
			},
			{
				label: 'Fecha guardado',
				value: 'attributes.fecha_guardado',
				field: 'fecha_guardado',
				align: 'left',
			},
			{
				label: 'Revisor',
				value: 'attributes.revisor',
				field: 'revisor',
				align: 'left',
			},
			{
				label: 'Nro Préstamo',
				value: 'attributes.prmprnpre',
				field: 'prmprnpre',
				align: 'left',
			},
			{
				label: 'Cod. Prod',
				value: 'attributes.prmprtcre',
				field: 'prmprtcre',
				align: 'left',
			},
			{
				label: 'Producto',
				value: 'attributes.prtcrdesc',
				field: 'prtcrdesc',
				align: 'left',
			},
			{
				label: 'Fecha Desemb',
				value: 'attributes.prmprfdes',
				field: 'prmprfdes',
				align: 'left',
			},
			{
				label: 'Monto Desemb',
				value: 'attributes.prmprmdes',
				field: 'prmprmdes',
				align: 'left',
			},
			{
				label: 'Saldo',
				value: 'attributes.prmprsald',
				field: 'prmprsald',
				align: 'left',
			},
			{
				label: 'Cod. Moneda',
				value: 'attributes.prmprcmon',
				field: 'prmprcmon',
				align: 'left',
			},
			{
				label: 'Moneda',
				value: 'attributes.moneda',
				field: 'moneda',
				align: 'left',
			},
			{
				label: 'Cod. Estado',
				value: 'attributes.prmprstat',
				field: 'prmprstat',
				align: 'left',
			},
			{
				label: 'Estado',
				value: 'attributes.estado',
				field: 'estado',
				align: 'left',
			},
			{
				label: 'Plazo',
				value: 'attributes.prmprplaz',
				field: 'prmprplaz',
				align: 'left',
			},
			{
				label: 'Cod. Reg.',
				value: 'attributes.prmprplaz',
				field: 'prmprplaz',
				align: 'left',
			},
			{
				label: 'Regional',
				value: 'attributes.plaza',
				field: 'plaza',
				align: 'left',
			},
			{
				label: 'Cod. Agen.',
				value: 'attributes.prmpragen',
				field: 'prmpragen',
				align: 'left',
			},
			{
				label: 'Agencia',
				value: 'attributes.agencia',
				field: 'agencia',
				align: 'left',
			},
			{
				label: 'Cod. Aut.',
				value: 'attributes.prmprautp',
				field: 'prmprautp',
				align: 'left',
			},
			{
				label: 'Autoriza',
				value: 'attributes.autoriza',
				field: 'autoriza',
				align: 'left',
			},
			{
				label: 'Cod. Asesor',
				value: 'attributes.prmprrseg',
				field: 'prmprrseg',
				align: 'left',
			},
			{
				label: 'Asesor',
				value: 'attributes.asesor',
				field: 'asesor',
				align: 'left',
			},
		],
		sort: [],
	};
	let store = new ApiStore({
		url: 'muestra',
		root: 'data',
		limit: 200,
		query: {
			per_page: JSONApiPerPageHandler,
			page: JSONApiPageHandler,
			sort: JSONApiSortHandler,
		},
	});
	let toolbar: CrudAction[] = [
	];
</script>

<Crud {config} {store} {toolbar} />
