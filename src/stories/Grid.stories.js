import ApiStore, { JSONApiSortHandler } from '../lib/ApiStore';
import ArrayStore from '../lib/ArrayStore';
import Grid from './Grid.svelte';

export default {
	title: 'Example/Grid',
	component: Grid,
	argTypes: {
		edit: { action: 'edit' },
		delete: { action: 'delete' },
	},
};

const Template = (args) => ({
	Component: Grid,
	props: args,
	on: {
		edit: args.edit,
		delete: args.delete,
	},
});

export const FromArray = Template.bind({});
FromArray.args = {
	config: {
		headers: [
			{
				label: 'Name',
				value: 'attributes.name',
				sortable: true,
				field: 'attributes.name',
				align: 'left',
			},
			{
				label: 'Age',
				value: 'attributes.age',
				sortable: true,
				field: 'attributes.age',
				align: 'center',
			},
			{
				label: 'Double Age',
				value: 'attributes.age*2',
				sortable: true,
				field: 'attributes.age',
				align: 'center',
			},
			{
				label: 'Amount',
				value: 'Math.random()*1000',
				format: ['currency'],
				align: 'right',
			},
			{
				label: '',
				value: '["edit", "delete"]',
				control: 'actions',
				align: 'center',
			},
		],
		sort: [],
	},
	store: new ArrayStore([
		{ attributes: { name: 'Jimmy Mcgill', age: 45 } },
		{ attributes: { name: 'Kim Wexler', age: 40 } },
		{ attributes: { name: 'Ignacio Varga', age: 37 } },
	]),
};

export const FromApi = Template.bind({});
FromApi.args = {
	config: {
		headers: [
			{
				label: 'ID',
				value: 'id',
				sortable: true,
				field: 'id',
				align: 'left',
			},
			{
				label: 'First Name',
				value: 'first_name',
				sortable: true,
				field: 'first_name',
				align: 'left',
			},
			{
				label: 'Last name',
				value: 'last_name',
				sortable: true,
				field: 'last_name',
				align: 'left',
			},
			{
				label: 'Phone Number',
				value: 'phone_number',
				align: 'right',
			},
			{
				label: '',
				value: '["edit", "delete"]',
				control: 'actions',
				align: 'center',
			},
		],
		sort: [],
	},
	store: new ApiStore({
		url: 'https://random-data-api.com/api/users/random_user?size=5',
	}),
	toolbar: [
		// @todo
		// {
		// 	label: 'Refresh',
		// 	icon: 'refresh',
		// 	action: ({ store }) => { console.log(store); store.refresh()},
		// },
	],
};

export const PokemonApi = Template.bind({});
PokemonApi.args = {
	config: {
		headers: [
			{
				label: 'Name',
				value: 'name',
				sortable: true,
				field: 'name',
				align: 'left',
			},
			{
				label: 'URL',
				value: 'url',
				sortable: true,
				field: 'url',
				align: 'left',
			},
		],
		sort: [],
	},
	store: new ApiStore({
		url: 'https://pokeapi.co/api/v2/pokemon',
		root: 'results',
		limit: 25,
		query: {
			limit: (/** @type {{ limit: number; }} */ { limit }) => limit,
			offset: (/** @type {{ offset: number; }} */ { offset }) => offset,
		},
	}),
};

export const PreguntasApi = Template.bind({});
PreguntasApi.args = {
	config: {
		headers: [
			{
				label: 'Grupo',
				value: 'attributes.id_grupo',
				sortable: true,
				field: 'id_grupo',
				groupRows: true,
				align: 'center',
			},
			{
				label: '#',
				value: 'attributes.number',
				sortable: true,
				field: 'number',
				align: 'center',
			},
			{
				label: 'Indice',
				value: 'attributes.indice',
				sortable: true,
				field: 'indice',
				align: 'left',
			},
			{
				label: 'Descripción',
				value: 'attributes.descripcion',
				sortable: true,
				field: 'descripcion',
				align: 'left',
				width: '100%',
			},
		],
		sort: [
			{
				field: 'number',
				direction: 'asc',
			},
		],
	},
	store: new ApiStore({
		url: 'http://localhost/projects/callizaya2/public/api.php/ob3/preguntas',
		root: 'data',
		limit: 200,
		query: {
			per_page: (/** @type {{ limit: number; }} */ { limit }) => limit,
			page: (/** @type {{ offset: number; limit: number; }} */ { offset, limit }) =>
				offset / limit + 1,
			sort: JSONApiSortHandler,
			filter: ['tipo_credito(2)'],
		},
	}),
};
