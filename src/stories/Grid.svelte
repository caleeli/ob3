<script lang="ts">
	import type GridConfig from 'src/lib/GridConfig';
	import Grid from '../lib/Grid';
	import '../lib/icons/FluentSystemIcons-Regular.css';
	import { Button } from 'fluent-svelte';
	import { createEventDispatcher } from 'svelte';
	import type StoreInterface from 'src/lib/StoreInterface';
	import Visibility from '../lib/Visibility.svelte';

	const dispatch = createEventDispatcher();

	export let config: GridConfig;
	let value: any[] = [];
	// Better Call Saul characters
	export let store: StoreInterface;
	let grid = new Grid(config, store);
	grid.load().then(() => {
		grid = grid;
	});
	async function sortBy(sortBy: string) {
		config.sort.field = sortBy;
		config.sort.order = config.sort.order === 'asc' ? 'desc' : 'asc';
		await grid.sort(config.sort.field, config.sort.order);
		grid = grid;
	}
	async function doAction(action: string, row: number) {
		const data = grid.getRowData(row);
		dispatch(action, data);
	}
	async function loadNextPage() {
		await grid.loadNextPage();
		grid = grid;
	}
</script>

<table>
	<tr>
		{#each config.headers as header}
			<th align={header.align || 'left'} on:click={() => header.sortBy && sortBy(header.sortBy)}>
				{header.label}
				<i
					class={`icon icon-ic_fluent_arrow_sort_${
						config.sort.order === 'desc' ? 'down' : 'up'
					}_16_regular ${config.sort.field === header.sortBy ? 'visible' : 'hidden'}`}
				/>
			</th>
		{/each}
	</tr>
	{#if grid}
		{#each grid.cell as data, row}
			<tr>
				{#each config.headers as header, col}
					{#if grid.cell[row] && grid.firstInGroup(row, col)}
						<td
							align={header.align}
							rowspan={grid.rowspan(row, col)}
							colspan={grid.colspan(row, col)}
						>
							{#if header.control === 'actions'}
								<div role="group">
									{#each grid.formatted(row, col) as action}
										<Button variant="hyperlink" on:click={() => doAction(action, row)}>
											<i class={`icon icon-ic_fluent_${action}_16_regular`} />
										</Button>
									{/each}
								</div>
							{:else}
								{grid.formatted(row, col)}
							{/if}
						</td>
					{/if}
				{/each}
			</tr>
		{/each}
	{/if}
</table>
{#if grid.error}
	<div class="error">{grid.error}</div>
{/if}

<Visibility
	steps={100}
	let:percent
	let:unobserve
	let:intersectionObserverSupport
	on:complete={loadNextPage}
>
	{#if !intersectionObserverSupport}
		<div>???</div>
	{:else}
		<div>...</div>
	{/if}
</Visibility>

<style>
	table {
		border-collapse: collapse;
		border-spacing: 0;
		width: 100%;
		font-size: var(--fds-body-font-size);
		cursor: default;
	}
	th {
		padding: 0.5rem;
		position: sticky;
		top: 0;
		background-color: #fff;
	}
	th:hover {
		background-color: #eee;
		-webkit-user-select: none;
		-khtml-user-select: none;
		-moz-user-select: -moz-none;
		-o-user-select: none;
		user-select: none;
	}
	.hidden {
		visibility: hidden;
	}
	td {
		border-top: 1px solid #eee;
		border-bottom: 1px solid #eee;
		padding: 0.5rem;
	}
	tr:hover td {
		background-color: #eee;
	}
	.error {
		color: red;
	}
	:global(div[role='group'] button + button) {
		margin-left: 0px;
	}
</style>
