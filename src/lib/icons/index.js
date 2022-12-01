import planificacion_auditoria from './planificacion_auditoria.svelte';
import seleccion_muestra from './seleccion_muestra.svelte';
import resumen_informes from './resumen_informes.svelte';
import reportes_auditoria from './reportes_auditoria.svelte';
import reporte_revision_auditoria from './reporte_revision_auditoria.svelte';
import reporte_visitas from './reporte_visitas.svelte';
import reporte_observaciones from './reporte_observaciones.svelte';
import lista_revision from './lista_revision.svelte';
import lista_revision_final from './lista_revision_final.svelte';
import lista_revision_preliminar from './lista_revision_preliminar.svelte';
import seguimiento_auditoria from './seguimiento_auditoria.svelte';
import docx from './docx.svelte';
import xlsx from './xlsx.svelte';
import pdf from './pdf.svelte';
import file from './file.svelte';

export default [
	{
		name: 'planificacion_auditoria',
		component: planificacion_auditoria,
	},
	{
		name: 'seleccion_muestra',
		component: seleccion_muestra,
	},
	{
		name: 'resumen_informes',
		component: resumen_informes,
	},
	{
		name: 'reportes_auditoria',
		component: reportes_auditoria,
	},
	{
		name: 'reporte_revision_auditoria',
		component: reporte_revision_auditoria,
	},
	{
		name: 'reporte_visitas',
		component: reporte_visitas,
	},
	{
		name: 'reporte_observaciones',
		component: reporte_observaciones,
	},
	{
		name: 'lista_revision',
		component: lista_revision,
	},
	{
		name: 'lista_revision_final',
		component: lista_revision_final,
	},
	{
		name: 'lista_revision_preliminar',
		component: lista_revision_preliminar,
	},
	{
		name: 'seguimiento_auditoria',
		component: seguimiento_auditoria,
	},
	{
		name: 'docx',
		component: docx,
	},
	{
		name: 'xlsx',
		component: xlsx,
	},
	{
		name: 'pdf',
		component: pdf,
	},
	{
		name: 'file',
		component: file,
	},
];
