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
	function gotoPage(event) {
		goto(event.detail.attributes.page);
	}
</script>

<div class="menu">
	<TreeView {store} converter={menu2node} on:select={gotoPage} show_root={false} is_root={true} />
</div>

<style>
	.menu {
		height: 100%;
		border-right: 3px double var(--fds-control-alt-fill-tertiary);
		padding-right: 1rem;
		overflow: auto;
		height: 100%;
		width: 35rem;
	}
</style>
