<script lang="ts">
	import type StoreInterface from '../lib/StoreInterface';
	import { createEventDispatcher } from 'svelte';
	import '../lib/icons/FluentSystemIcons-Regular.css';
	import type TreeNode from '../lib/TreeNode';

	const dispatch = createEventDispatcher();

	export let store: StoreInterface;
	export let root_node: string = 'root';
	export let is_root = true;
	export let editable = false;
	export let tree: TreeNode = {
		label: '',
		children: [],
		selected: false,
		icon: '',
		open: true,
		color: 'black',
	};
	export let root: TreeNode = {
		label: '',
		children: [],
		selected: false,
		icon: '',
		open: true,
		color: 'black',
	};
	export let converter: (
		currentRow: any,
		allRows: any[],
		converter: (currentRow: any, allRows: any[]) => TreeNode
	) => TreeNode;

	let icons: { [key: string]: string } = {
		undefined: 'home',
		'': 'home',
		folder: 'folder',
		file: 'file',
		component: 'cube',
		building: 'box-open',
		processor: 'microchip',
		call_processor: 'download',
		import: 'file-import',
	};
	const toggleExpansion = (node: TreeNode) => {
		node.open = !node.open;
		root.selected = false;
		unselectChildren(root);
		node.selected = true;
		tree = tree;
		dispatch('select', node);
	};
	function selectOne(node: TreeNode) {
		root.selected = false;
		unselectChildren(root);
		node.selected = true;
		dispatch('select', node.data);
		tree = tree;
	}
	function unselectChildren(node: TreeNode) {
		if (node.children) {
			node.children.forEach((child) => {
				child.selected = false;
				unselectChildren(child);
			});
		}
	}
	function row2node(currentRow: any, allRows: any[]): TreeNode {
		return converter(currentRow, allRows, row2node);
	}
	if (store) {
		store.get().then((data) => {
			root = row2node(
				{
					id: root_node,
				},
				data
			);
			tree = root;
		});
	}
</script>

<ul class={is_root ? 'root' : 'child'}>
	<!-- transition:slide -->
	<li>
		{#if tree.children && tree.children.length > 0}
			<div on:click={() => toggleExpansion(tree)} class={`${(tree.selected && 'selected') || ''}`}>
				<i
					class={`icon icon-ic_fluent_${tree.open ? 'chevron_down' : 'chevron_right'}_20_regular`}
				/>
				{#if editable}
					<input type="checkbox" on:click|stopPropagation bind:checked={tree.selected} />
				{/if}
				<i
					class={`icon icon-ic_fluent_${icons[tree.icon] || tree.icon}_20_regular`}
					style={`color: ${tree.color};`}
				/>
				{#if editable}
					<input
						class="node-name"
						bind:value={tree.label}
						size={Math.max(5, tree.label.length)}
						on:click|stopPropagation={() => selectOne(tree)}
					/>
				{:else}
					<span class="node-name">{tree.label}</span>
				{/if}
			</div>
			{#if tree.open}
				{#each tree.children as child}
					<svelte:self bind:tree={child} is_root={false} bind:root on:select />
				{/each}
			{/if}
		{:else}
			<div
				on:click|stopPropagation={() => selectOne(tree)}
				class={`${(tree.selected && 'selected') || ''}`}
			>
				{#if editable}
					<input type="checkbox" on:click|stopPropagation bind:checked={tree.selected} />
				{/if}
				<i
					class={`icon icon-ic_fluent_${icons[tree.icon] || tree.icon}_20_regular`}
					style={`color: ${tree.color};`}
				/>
				{#if editable}
					<input
						class="node-name"
						bind:value={tree.label}
						size={Math.max(5, tree.label.length)}
						on:click|stopPropagation={() => selectOne(tree)}
					/>
				{:else}
					<span class="node-name">{tree.label}</span>
				{/if}
			</div>
		{/if}
	</li>
</ul>

<style>
	ul.root {
		margin: 0;
		padding: 0;
	}
	ul.child {
		margin: 0 0 0 0.5rem;
		padding-left: 0.7rem;
		border-left: 1px dashed var(--fds-control-strong-fill-default);
	}
	ul {
		list-style: none;
		user-select: none;
		cursor: pointer;
	}
	li > div {
		white-space: nowrap;
	}
	li > div:hover {
		background-color: var(--fds-solid-background-tertiary);
	}
	.no-arrow {
		padding-left: 1rem;
	}
	.arrow {
		cursor: pointer;
		display: inline-block;
		/* transition: transform 200ms; */
	}
	.selected {
		background-color: hsla(var(--fds-accent-dark-1), 20%);
	}
</style>
