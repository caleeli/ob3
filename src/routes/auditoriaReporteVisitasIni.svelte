<script lang="ts">
	import type CrudAction from '../lib/CrudAction';
	import type FormField from '../lib/FormField';
	import Form from '../stories/Form.svelte';
	import ApiStore, {
		JSONApiPageHandler,
		JSONApiPerPageHandler,
		JSONApiSortHandler,
	} from '../lib/ApiStore';
	import type GridConfig from '../lib/GridConfig';
	import Crud from '../stories/CRUD.svelte';
	import { comboStore } from '../lib/helpers';

	let content: FormField[][] = [
		[
			{
				control: 'TextBox',
				label: 'Informe Desde',
				name: 'informe_desde',
				type: 'text',
			},
			{
				control: 'TextBox',
				label: 'Informe Hasta',
				name: 'informe_hasta',
				type: 'text',
			},
		],
		[
			{
				control: 'ComboBox',
				label: 'Sucursal Desde',
				name: 'sucursal_desde',
				...comboStore('sucursales'),
			},
			{
				control: 'ComboBox',
				label: 'Sucursal Hasta',
				name: 'sucursal_hasta',
				...comboStore('sucursales'),
			},
		],
		[
			{
				control: 'ComboBox',
				label: 'Elaborado por (Desde)',
				name: 'elaborado_desde',
				...comboStore('users'),
			},
			{
				control: 'ComboBox',
				label: 'Elaborado por (Hasta)',
				name: 'elaborado_hasta',
				...comboStore('users'),
			},
		],
		[
			{
				control: 'ComboBox',
				label: 'Calidad',
				name: 'calidad',
				...comboStore('calidades'),
			},
			{
				control: 'ComboBox',
				label: 'Tipo de Informe',
				name: 'tipo_informe',
				...comboStore('tipos_informe'),
			},
		],
		[
			{
				control: 'Button',
				label: 'Generar Reporte',
				variant: 'accent',
			},
		],
	];
	let data = {};
</script>

<Form {content} {data} />
