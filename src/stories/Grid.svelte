<script lang="ts">
	import type GridConfig from 'src/lib/GridConfig';
	import Grid from '../lib/Grid';
	import '../lib/icons/FluentSystemIcons-Regular.css';
	import { Button } from 'fluent-svelte';
	import { createEventDispatcher } from 'svelte';
	import { edit_mode } from '../store';
	import type StoreInterface from '../lib/StoreInterface';
	import Visibility from '../lib/Visibility.svelte';
	import EditProperties from '../lib/EditProperties.svelte';
	import type FormField from '../lib/FormField';
	import type ConfigStore from '../lib/ConfigStore';

	const dispatch = createEventDispatcher();

	export let config: GridConfig;
	export let store: StoreInterface;
	export let selected: any[] = [];
	export let configStore: ConfigStore | undefined = undefined;

	let grid = new Grid(config, store);
	load();
	store.onrefresh((data: any[]) => {
		grid.loadFromData(data);
		grid = grid;
	});
	async function load() {
		try {
			await grid.load();
		} catch (err: any) {
			grid.error = err.message || err;
		}
		grid = grid;
	}
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
		for (let r = row, l = row + rows; r < l; r++) {
			grid.rowGroup[r].open = !grid.rowGroup[r].open;
		}
		grid = grid;
	}
	function toggleSelect(row: any) {
		if (config.multiSelect) {
			if (selected.includes(row)) {
				selected = selected.filter((r) => r !== row);
			} else {
				selected.push(row);
			}
		} else {
			selected = [row];
		}
		grid = grid;
	}
	function clickHeader(header: any, header_index: number) {
		if (isEditMode) {
			editHeader = true;
			editHeaderIndex = header_index;
			editConfig = config.headers[editHeaderIndex];
		}
		return header.sortable && toggleSort(header.field);
	}

	let editConfig = {};
	let properties: FormField[][] = [
		[
			{
				control: 'TextBox',
				label: 'Field',
				name: 'field',
				type: 'text',
			},
		],
		[
			{
				control: 'TextBox',
				label: 'Label',
				name: 'label',
				type: 'text',
			},
		],
		[
			{
				control: 'TextBox',
				label: 'Value',
				name: 'value',
				type: 'text',
			},
		],
		[
			{
				control: 'ComboBox',
				label: 'Align',
				name: 'align',
				options: [
					{
						name: 'Left',
						value: 'left',
					},
					{
						name: 'Center',
						value: 'center',
					},
					{
						name: 'Right',
						value: 'right',
					},
				],
			},
		],
	];
	let editHeader = false;
	let editHeaderIndex = 0;
	let isEditMode = false;
	let dragColumnIndex = -1;
	let dragOverIndex = -1;
	edit_mode.subscribe((edit_mode) => {
		isEditMode = edit_mode;
	});
	function updateHeader() {
		config = config;
		load();
		saveConfig();
	}
	function dragStartColumn(e: DragEvent & { currentTarget: EventTarget }, header_index: number) {
		dragColumnIndex = header_index;
		e.dataTransfer && (e.dataTransfer.effectAllowed = 'move');
	}
	function dragOverColumn(e: DragEvent & { currentTarget: EventTarget }, header_index: number) {
		e.preventDefault();
		dragOverIndex = header_index;
	}
	function dropColumn(dropColumnIndex: number) {
		if (dragColumnIndex > dropColumnIndex) {
			config.headers.splice(dropColumnIndex, 0, config.headers.splice(dragColumnIndex, 1)[0]);
		} else {
			config.headers.splice(dropColumnIndex, 0, config.headers.splice(dragColumnIndex, 1)[0]);
		}
		dragOverIndex = -1;
		config = config;
		load();
		saveConfig();
	}
	function saveConfig() {
		if (!configStore) {
			return;
		}
		configStore.save();
	}
</script>

<EditProperties form={properties} data={editConfig} bind:open={editHeader} on:save={updateHeader} />

<table>
	<tr>
		{#each config.headers as header, header_index}
			<th
				class={`${isEditMode ? 'editable' : ''} ${
					dragOverIndex === header_index
						? 'drag-over-' + (dragColumnIndex > dragOverIndex ? 'left' : 'right')
						: ''
				}`}
				style={`text-align:${header.align || 'left'};`}
				width={header.width || ''}
				on:click={() => clickHeader(header, header_index)}
				draggable={isEditMode}
				on:dragstart={(e) => dragStartColumn(e, header_index)}
				on:dragover={(e) => dragOverColumn(e, header_index)}
				on:drop={(e) => dropColumn(header_index)}
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
			{#if grid.cell[row] && config.headers[0].groupRows && grid.firstInGroup(row, 0)}
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
			<tr
				class={`${selected.includes(data) ? 'active' : ''} ${
					grid.rowGroup[row].open ? '' : 'closed'
				}`}
			>
				{#each config.headers as header, col}
					{#if grid.cell[row]}
						<td
							class={`${config.headers[col].groupRows ? 'grouped' : ''}`}
							style={`text-align:${header.align || 'left'};`}
							on:click={() => toggleSelect(data)}
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
		<div />
	{:else}
		<div>...</div>
	{/if}
</Visibility>

<style>
	table {
		border-collapse: collapse;
		border-spacing: 0;
		width: max-content;
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
		background-color: var(--fds-solid-background-tertiary);
	}
	tr.active td {
		background-color: hsla(var(--fds-accent-dark-1), 20%);
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
	th.editable {
		cursor: crosshair;
	}
	th.editable::after {
		content: '✍';
		position: absolute;
		right: 0;
	}
	th.drag-over-left {
		border-left: 3px double var(--fds-solid-background-secondary);
	}
	th.drag-over-right {
		border-right: 3px double var(--fds-solid-background-secondary);
	}
</style>
