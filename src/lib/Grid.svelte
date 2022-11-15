<script lang="ts">
	import type GridConfig from 'src/lib/GridConfig';
	import Grid from './Grid';
	import '../lib/icons/FluentSystemIcons-Regular.css';
	import { Button } from 'fluent-svelte';
	import { createEventDispatcher } from 'svelte';
	import { edit_mode } from '../store';
	import type StoreInterface from './StoreInterface';
	import Visibility from './Visibility.svelte';
	import EditProperties from './EditProperties.svelte';
	import type FormField from './FormField';
	import type ConfigStore from './ConfigStore';
	import type ApiStore from './ApiStore';
	import type CrudAction from './CrudAction';
	import { translation as __ } from './translations';
	import { format } from 'sql-formatter';

	const dispatch = createEventDispatcher();

	export let config: GridConfig;
	export let store: StoreInterface | ApiStore;
	export let selected: any[] = [];
	export let configStore: ConfigStore | undefined = undefined;
	export let toolbar: CrudAction[] = [];
	export let rowActions: CrudAction[] = [];

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
		const tool = toolbar.find((t) => t.action === action);
		dispatch(action, { tool, selected: data });
		dispatch("action", { action, tool, selected: data });
	}
	async function loadNextPage() {
		await grid.loadNextPage();
		grid = grid;
	}
	function toggleRowGroup(col: number, value: any) {
		grid.collapse(col, value);
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
			if (config.onselect) {
				const tool = rowActions.find((t) => t.action === config.onselect);
				dispatch('select', { tool, selected: row });
			}
		}
		grid = grid;
	}
	function clickHeader(
		header: any,
		header_index: number,
		e: MouseEvent & { currentTarget: EventTarget }
	) {
		if (isEditMode) {
			// check if ctrl is pressed
			if (e.ctrlKey) {
				editHeaderDuplicateConfig(header_index);
			} else {
				editHeaderConfig(header_index);
			}
		}
		return header.sortable && toggleSort(header.field);
	}

	let editConfigData = {};
	let editConfigForm: FormField[][] = [];
	let editConfigModelAttributes: { name: string; value: string }[] = [];
	let editConfigFormHeader: FormField[][] = [
		[
			{
				control: 'ComboBox',
				label: 'Field',
				options: editConfigModelAttributes,
				name: 'field',
				async action(value) {
					config.headers[editHeaderIndex].value = `attributes.${value}`;
				},
			},
			{
				control: 'TextBox',
				label: 'Value',
				name: 'value',
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
		[
			{
				control: 'Checkbox',
				label: 'Group rows',
				name: 'groupRows',
			},
			{
				control: 'Checkbox',
				label: 'Sortable',
				name: 'sortable',
			},
			{
				control: 'Checkbox',
				label: 'Currency',
				name: 'format',
				getter(field: string[] | undefined) {
					return field && field.includes('currency');
				},
				setter(field: string[] | undefined, value: any) {
					field = [];
					if (value) {
						field.push('currency');
					} else {
						field = field.filter((f: string) => f !== 'currency');
					}
					return field;
				},
			},
		],
		[
			{
				control: 'ComboBox',
				label: 'Sort',
				name: 'sorted',
				options: [
					{
						name: '',
						value: '',
					},
					{
						name: 'Ascending',
						value: 'asc',
					},
					{
						name: 'Descending',
						value: 'desc',
					},
				],
			},
		],
		[
			{
				control: 'Button',
				label: 'Duplicate',
				variant: 'standard',
				async action() {
					// duplicate header editHeaderIndex
					editHeaderDuplicateConfig(editHeaderIndex);
					editConfig = false;
				},
			},
			{
				control: 'Button',
				label: 'Remove',
				variant: 'standard',
				async action() {
					// remove header editHeaderIndex
					config.headers.splice(editHeaderIndex, 1);
					updateConfig();
					editConfig = false;
				},
			},
		],
	];
	let editConfigFormGrid: FormField[][] = [
		[
			{
				control: 'TextBox',
				label: 'Model URL',
				name: 'store.config.url',
			},
		],
		[
			{
				control: 'Checkbox',
				label: 'Multi Select',
				name: 'config.multiSelect',
			},
			{
				control: 'TextBox',
				label: 'Sort by',
				name: 'config.sort',
				getter(
					field:
						| {
								field: string;
								direction: 'asc' | 'desc';
						  }[]
						| undefined
				) {
					return field && field.map((f) => `${f.field} ${f.direction}`).join(',');
				},
				setter(
					field:
						| {
								field: string;
								direction: 'asc' | 'desc';
						  }[]
						| undefined,
					value: any
				) {
					field = [];
					if (value) {
						field = value.split(',').map((s: string) => {
							let [field, direction] = s.trim().split(' ');
							direction = direction || 'asc';
							return { field, direction };
						});
					}
					return field;
				},
			},
		],
		[
			{
				control: 'TextArea',
				label: 'Query',
				name: '$query',
				getter() {
					const meta = store.getMeta();
					const query = meta ? meta.query : "";
					const params = meta ? { ...meta.params } : {};
					const keys = Object.keys(params);
					keys.forEach((key) => {
						params[key] = JSON.stringify(params[key]);
					});
					return format(query, {
						language: 'plsql',
						params,
					});
				},
				setter() {
					return;
				},
			},
			/*{
				control: 'TextArea',
				label: 'Params',
				name: '$params',
				getter(field: string | undefined) {
					return JSON.stringify(store.getMeta().params, null, 2);
				},
				setter(field: string | undefined, value: any) {
					return;
				},
			},*/
		],
		[
			{
				control: 'Button',
				label: 'Run Query',
				variant: 'standard',
				async action() {
					// do nothing
				},
			},
		],
		[
			{
				control: 'Button',
				label: 'Add Toolbar Button',
				variant: 'standard',
				async action() {
					toolbar.push({
						icon: 'add',
						label: 'New Button',
						action: 'new',
					});
					updateConfig();
				},
			},
			{
				control: 'Button',
				label: 'Add On Select Action',
				variant: 'standard',
				async action() {
					if (!config.onselect) {
						config.onselect = 'selected';
						const hasSelctedAction = rowActions.find((a) => a.action === 'selected');
						if (!hasSelctedAction) {
							rowActions.push({
								action: 'selected',
								form: [],
							});
						}
						updateConfig();
					}
				},
			},
		],
	];
	let editConfigFormToolbarButton: FormField[][] = [
		[
			{
				control: 'ComboBox',
				label: 'Icon',
				name: 'icon',
				options: [
					{
						name: 'add',
						value: 'add',
					},
					{
						name: 'delete',
						value: 'delete',
					},
					{
						name: 'edit',
						value: 'edit',
					},
					{
						name: 'open',
						value: 'open',
					},
					{
						name: 'save',
						value: 'save',
					},
					{
						name: 'search',
						value: 'search',
					},
				],
			},
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
				label: 'Href link',
				name: 'href',
				type: 'text',
			},
		],
		[
			{
				control: 'TextBox',
				label: 'Action',
				name: 'action',
				type: 'text',
			},
		],
		[
			{
				control: 'Button',
				label: 'Delete',
				name: 'delete',
				variant: 'standard',
				async action() {
					const editToolbarIndex = toolbar.indexOf(editConfigData);
					toolbar.splice(editToolbarIndex, 1);
					updateConfig();
					editConfig = false;
				},
			},
		],
	];
	let editConfigTitle = '';
	let editConfig = false;
	let editHeaderIndex = 0;
	let isEditMode = false;
	let dragColumnIndex = -1;
	let dragOverIndex = -1;
	edit_mode.subscribe((edit_mode) => {
		isEditMode = edit_mode;
	});
	function updateConfig() {
		config = config;
		toolbar = toolbar;
		load();
		saveConfig();
	}
	function editHeaderDuplicateConfig(header_index: number) {
		const header = config.headers[header_index];
		const newHeader = { ...header };
		config.headers.splice(header_index + 1, 0, newHeader);
		updateConfig();
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
	function editHeaderConfig(header_index: number) {
		editConfigForm = editConfigFormHeader;
		editConfig = true;
		editHeaderIndex = header_index;
		editConfigData = config.headers[editHeaderIndex];
	}
	function editGridConfig() {
		editConfigForm = editConfigFormGrid;
		editConfig = true;
		editConfigData = { config, store };
	}
	function saveConfig() {
		if (!configStore) {
			return;
		}
		configStore.save();
	}
	if (config && configStore) {
		configStore
			.getModelMeta(store.config.url)
			.then((model) => {
				const keys = Object.keys(model.attributes);
				editConfigModelAttributes.splice(0);
				editConfigModelAttributes.push({ name: '', value: '' });
				editConfigModelAttributes.push(
					...keys.map((key) => {
						return { name: key, value: key };
					})
				);
				editConfigFormHeader = editConfigFormHeader;
			})
			.catch(() => {
				editConfigModelAttributes.splice(0);
			});
	}
	function doToolbarAction(tool: CrudAction) {
		if (isEditMode) {
			editConfigForm = editConfigFormToolbarButton;
			editConfig = true;
			editConfigData = tool;
			return;
		}
		dispatch('toolbar', { tool, selected: config.multiSelect ? selected : selected[0], store });
	}
	// e.g. auditoriaRevision/${selected.id}
	function href(tool: CrudAction, selected: any[]) {
		if (!tool.href) {
			return;
		}
		const fn = new Function('selected', 'try{return `' + tool.href + '`}catch(e){return null}');
		return fn(config.multiSelect ? selected : selected[0]);
	}
</script>

<EditProperties
	title={editConfigTitle}
	form={editConfigForm}
	data={editConfigData}
	bind:open={editConfig}
	on:save={updateConfig}
/>

<table>
	<thead>
		<tr>
			<th class="toolbar" colspan={config.headers.length}>
				<div class="toolbar">
					{#each toolbar as tool}
						<Button
							variant="hyperlink"
							on:click={() => doToolbarAction(tool)}
							href={href(tool, selected)}
						>
							<i class={`icon icon-ic_fluent_${tool.icon}_16_regular`} />
							{tool.label ? __(tool.label) : ''}
						</Button>
					{/each}
					{#if isEditMode}
						<Button on:click={editGridConfig}>⚙️</Button>
					{/if}
				</div>
			</th>
		</tr>
	</thead>
	<tbody>
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
					on:click={(e) => clickHeader(header, header_index, e)}
					draggable={isEditMode}
					on:dragstart={(e) => dragStartColumn(e, header_index)}
					on:dragover={(e) => dragOverColumn(e, header_index)}
					on:drop={() => dropColumn(header_index)}
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
				{#each config.headers as header, col}
					{#if grid.cell[row] && header.groupRows && grid.firstInGroup(row, col)}
						<tr>
							<td
								colspan={config.headers.length}
								rowspan="1"
								on:click={() => toggleRowGroup(col, grid.cell[row][col])}
							>
								<i
									class={`icon icon-ic_fluent_${
										grid.isCollapsed(row) ? 'chevron_right' : 'chevron_down'
									}_20_regular`}
								/>
								<b>
									{config.headers[col].label}:
									{grid.formatted(row, col)}
								</b>
							</td>
						</tr>
					{/if}
				{/each}
				<tr
					class={`${selected.includes(grid.data[row]) ? 'active' : ''} ${
						grid.isCollapsed(row) ? 'closed' : ''
					}`}
				>
					{#each config.headers as header, col}
						{#if grid.cell[row]}
							<td
								class={`${config.headers[col].groupRows ? 'grouped' : ''}`}
								style={`text-align:${header.align || 'left'};`}
								on:click={() => toggleSelect(grid.data[row])}
							>
								{#if header.control === 'actions'}
									<div role="group">
										{#each grid.formatted(row, col) as action}
											<Button variant="hyperlink" on:click={() => doAction(action, row)}>
												<i class={`icon icon-ic_fluent_${action}_16_regular`} />
												{__(action)}
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
	</tbody>
	<tfoot>
		<tr>
			<td colspan={config.headers.length}>
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
			</td>
		</tr>
	</tfoot>
</table>

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
		top: calc(30px + 1rem);
		background-color: #fff;
		white-space: nowrap;
		z-index: 1;
	}
	th:hover {
		background-color: var(--fds-solid-background-tertiary);
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
		max-width: 40rem;
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
		opacity: 0.5;
	}
	th.editable {
		cursor: crosshair;
	}
	:global(.control-pressed) th.editable {
		cursor: copy;
	}
	th.editable::after {
		content: '⚙️';
		position: absolute;
		right: 0;
	}
	th.drag-over-left {
		border-left: 3px double var(--fds-solid-background-secondary);
	}
	th.drag-over-right {
		border-right: 3px double var(--fds-solid-background-secondary);
	}
	div.toolbar {
		min-height: 30px;
		display: flex;
		align-items: center;
		padding: 0.5rem;
		background-color: var(--fds-solid-background-base);
		z-index: 1;
	}
	th.toolbar {
		position: sticky;
		top: 0;
		padding: 0px;
	}
</style>
