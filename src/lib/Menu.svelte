<script lang="ts">
	import ApiStore from './ApiStore';
	import TreeView from '../stories/TreeView.svelte';
	import { goto } from '$app/navigation';

	let store = new ApiStore({
		url: 'menus',
		root: 'data',
		query: {
			per_page: 200,
			sort: 'position',
		},
	});
	let short = true;

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
		const label = currentRow.attributes?.text || '';
		// reemplazar acentos: á a, é e, í i, ó o, ú u
		const icon = label
			.toLowerCase()
			.replace(/á/g, 'a')
			.replace(/é/g, 'e')
			.replace(/í/g, 'i')
			.replace(/ó/g, 'o')
			.replace(/ú/g, 'u')
			.replace(/ /g, '_')
			.replace(/_de_/, '_');
		return {
			label,
			children,
			selected: false,
			open: !currentRow.attributes?.text,
			icon,
			color: children.length ? 'orangered' : 'steelblue',
			data: currentRow,
		};
	}
	function gotoPage(event: { detail: { attributes: { page: string | URL } } }) {
		if (event.detail.attributes) {
			goto(event.detail.attributes.page);
		}
	}
</script>

<div class={`menu ${short ? 'short' : ''}`}>
	<TreeView
		{store}
		converter={menu2node}
		on:select={gotoPage}
		show_root={false}
		is_root={true}
		{short}
	/>
</div>

<style>
	.menu {
		height: 100%;
		border-right: 3px double var(--fds-control-alt-fill-tertiary);
		padding-right: 1rem;
		overflow: auto;
		height: 100%;
		width: 35rem;
		-ms-overflow-style: none; /* IE and Edge */
		scrollbar-width: none; /* Firefox */
	}
	/* Hide scrollbar for Chrome, Safari and Opera */
	.menu::-webkit-scrollbar {
		display: none;
	}
	.short {
		padding-right: 0px;
		width: 3rem;
	}
</style>
