import ApiStore from '../lib/ApiStore';
import TreeView from './TreeView.svelte';

export default {
	title: 'Example/TreeView',
	component: TreeView,
	argTypes: {
		select: { action: 'select' },
	},
};

const Template = (args) => ({
	Component: TreeView,
	props: args,
	on: {
		select: args.select,
	},
});

/**
 * @param {any} currentRow
 * @param {any} allRows
 * @param {(arg0: any, arg1: any[]) => any} converter
 *
 * @returns {any}
 */
function menu2node(currentRow, allRows, converter) {
	const children = allRows
		.filter((/** @type {any} */ row_i) => row_i.attributes.parent == currentRow.id)
		.map((/** @type {any} */ row) => converter(row, allRows));
	return {
		label: currentRow.attributes?.text || '',
		children,
		selected: false,
		open: true,
		icon: children.length ? 'folder' : 'app_generic',
		color: children.length ? 'orangered' : 'steelblue',
		data: currentRow,
	};
}
export const Simple = Template.bind({});
Simple.args = {
	store: new ApiStore({
		url: 'http://localhost/projects/callizaya2/public/api.php/ob3/menus',
		root: 'data',
		query: {
			per_page: 200,
		},
	}),
	converter: menu2node,
};
