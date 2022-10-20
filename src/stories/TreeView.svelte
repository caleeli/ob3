<script lang="ts">
	import type StoreInterface from '../lib/StoreInterface';
	import { createEventDispatcher } from 'svelte';
	import '../lib/icons/FluentSystemIcons-Regular.css';
	import type TreeNode from '../lib/TreeNode';
	import Icon from '../lib/Icon.svelte';

	const dispatch = createEventDispatcher();

	// class property
	let className = '';
	export { className as class };
	export let store: StoreInterface;
	export let root_node: string = 'root';
	export let is_root = true;
	export let show_root = true;
	export let short = false;
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

<ul class={`${is_root ? 'root' : 'child'} ${className} ${short ? 'short' : ''}`}>
	<!-- transition:slide -->
	<li>
		{#if tree.children && tree.children.length > 0}
			<div
				on:click={() => toggleExpansion(tree)}
				class={`${(tree.selected && 'selected') || ''} ${
					short && is_root && !show_root ? 'hidden' : ''
				}`}
				title={tree.label}
			>
				{#if !short && (!is_root || show_root)}
					<i
						class={`icon icon-ic_fluent_${tree.open ? 'chevron_down' : 'chevron_right'}_20_regular`}
					/>
				{/if}
				{#if editable}
					<input type="checkbox" on:click|stopPropagation bind:checked={tree.selected} />
				{/if}
				{#if !is_root || show_root}
					<Icon icon={tree.icon} color={tree.color} />
				{/if}
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
					<svelte:self
						bind:tree={child}
						is_root={false}
						class={!show_root ? 'hide-line' : ''}
						{short}
						bind:root
						on:select
					/>
				{/each}
			{/if}
		{:else}
			<div
				on:click|stopPropagation={() => selectOne(tree)}
				class={`${(tree.selected && 'selected') || ''}`}
				title={tree.label}
			>
				{#if editable}
					<input type="checkbox" on:click|stopPropagation bind:checked={tree.selected} />
				{/if}
				<Icon icon={tree.icon} color={tree.color} />
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
	li > div.selected:hover {
		background-color: hsla(var(--fds-accent-dark-1), 30%);
	}
	.no-arrow {
		padding-left: 1rem;
	}
	.arrow {
		cursor: pointer;
		display: inline-block;
		/* transition: transform 200ms; */
	}
	li div {
		overflow: hidden;
		text-overflow: ellipsis;
	}
	.selected {
		background-color: hsla(var(--fds-accent-dark-1), 20%);
	}
	.hide-line {
		border-left: none !important;
		padding-left: 0px !important;
	}
	ul.short {
		border-left: none !important;
		padding-left: 0px;
		margin-left: 0.1rem;
		font-size: 1.5rem;
	}
	ul.short .node-name {
		display: none;
	}
	ul.short > li > div {
		text-align: center;
		padding-top: 0.7rem;
		padding-bottom: 0.35rem;
	}
	.hidden {
		display: none;
	}
</style>
