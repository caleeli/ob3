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
	export let store: StoreInterface;

	let value: any[] = [];
	let active: number = -1;
	let grid = new Grid(config, store);
	grid.load().then(() => {
		grid = grid;
	});
	store.onrefresh((data: any[]) => {
		grid.loadFromData(data);
		grid = grid;
	});
	async function toggleSort(sortBy: string | undefined) {
		if (!sortBy) {
			return;
		}
		await grid.toggleSort(sortBy);
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
	function toggleRowGroup(row: number, rows: number) {
		console.log(row, rows);
		for (let r = row, l = row + rows; r < l; r++) {
			grid.rowGroup[r].open = !grid.rowGroup[r].open;
			console.log(grid.rowGroup[r].open);
		}
		grid = grid;
	}
</script>

<table>
	<tr>
		{#each config.headers as header}
			<th
				align={header.align || 'left'}
				width={header.width || ''}
				on:click={() => header.sortable && toggleSort(header.field)}
			>
				{header.label}
				<i
					class={`icon icon-ic_fluent_arrow_sort_${
						grid.getDirection(header.field) === 'desc' ? 'down' : 'up'
					}_16_regular ${grid.getDirection(header.field) ? 'visible' : 'hidden'}`}
				/>
			</th>
		{/each}
	</tr>
	{#if grid}
		{#each grid.cell as data, row}
			{#if grid.cell[row] && grid.firstInGroup(row, 0)}
				<tr>
					<td
						colspan={config.headers.length}
						rowspan="1"
						on:click={() => toggleRowGroup(row, grid.rowspan(row, 0))}
					>
						<i
							class={`icon icon-ic_fluent_${
								grid.rowGroup[row].open ? 'chevron_down' : 'chevron_right'
							}_20_regular`}
						/>
						{grid.formatted(row, 0)}
					</td>
				</tr>
			{/if}
			<tr class={`${active == row ? 'active' : ''} ${grid.rowGroup[row].open ? '' : 'closed'}`}>
				{#each config.headers as header, col}
					{#if grid.cell[row]}
						<td
							class={`${config.headers[col].groupRows ? 'grouped' : ''}`}
							align={header.align}
							on:click={() => (active = active === row ? -1 : row)}
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
		empty-cells: show;
	}
	th {
		padding: 0.5rem;
		position: sticky;
		top: 0;
		background-color: #fff;
		white-space: nowrap;
		z-index: 1;
	}
	th:hover {
		background-color: var(--fds-solid-background-base);
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
		border-top: 1px solid var(--fds-solid-background-secondary);
		border-bottom: 1px solid var(--fds-solid-background-secondary);
		padding: 0.5rem;
	}
	tr:hover td {
		background-color: var(--fds-solid-background-secondary);
	}
	tr.active td {
		background-color: var(--fds-solid-background-secondary);
	}
	tr.closed {
		display: none;
	}
	.error {
		color: red;
	}
	:global(div[role='group'] button + button) {
		margin-left: 0px;
	}
	:global(div[role='group']) {
		white-space: nowrap;
	}
	.grouped {
		opacity: 0;
	}
</style>
