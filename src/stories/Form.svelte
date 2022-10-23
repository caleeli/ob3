<script lang="ts">
	import { Button, Checkbox, InfoBar } from 'fluent-svelte';
	import { TextBox } from 'fluent-svelte';
	import TextArea from '../lib/TextArea.svelte';
	import { PersonPicture } from 'fluent-svelte';
	import ComboBox from '../lib/ComboBox.svelte';
	import { get, set } from 'lodash';
	import type FormField from '../lib/FormField';
	import { translation as __ } from '../lib/translations';
	import { createEventDispatcher } from 'svelte';
	import ApiStore from '../lib/ApiStore';
	import { onMount } from 'svelte';
	import { edit_mode } from '../store';
	import EditProperties from '../lib/EditProperties.svelte';
	import type ConfigStore from '../lib/ConfigStore';
	import { comboStore } from '../lib/helpers';

	const dispatch = createEventDispatcher();

	export let title: string = '';
	export let content: FormField[][] = [];
	export let border: boolean = true;
	export let blur: boolean = false;
	export let data: any = {};
	export let error = '';
	export let store: ApiStore | undefined = undefined;
	export let configStore: ConfigStore | undefined = undefined;
	export let margin = '0px';

	// helpers used in: combo data source
	const helpers = {
		comboStore,
	};
	let inProgress: any[] = [];
	let internalValues: { cell: FormField; value: any }[] = [];
	let accessor = new Proxy(data, {
		get: (target, name) => {
			if (typeof name === 'string' && name.substring(0, 1) === '$') {
				const index = parseInt(name.substring(1));
				const cell = internalValues[index].cell;
				const field = cell.name ? get(target, cell.name) : internalValues[index].value;
				if (cell.getter) {
					return cell.getter(field, data);
				} else {
					return field;
				}
			} else {
				return get(data, name);
			}
		},
		set: (target, name, value) => {
			if (typeof name === 'string' && name.substring(0, 1) === '$') {
				const index = parseInt(name.substring(1));
				if (index >= 0 && index < internalValues.length) {
					internalValues[index].value = value;
					const cell = internalValues[index].cell;
					if (cell.setter) {
						const field = cell.name ? get(target, cell.name) : internalValues[index].value;
						const result = cell.setter(field, value, data);
						if (result && cell.name) {
							set(target, cell.name, result);
						}
					}
				} else {
					throw new Error(`Invalid internal value index: ${index}`);
				}
				return data;
			} else {
				return set(data, name, value);
			}
		},
	});
	let isEditMode = false;

	edit_mode.subscribe((edit_mode) => {
		isEditMode = !!configStore && edit_mode;
	});
	function submit() {
		dispatch('submit', data);
	}
	async function buttonAction(cell: FormField) {
		if (cell.action) {
			try {
				inProgress.push(cell.action);
				inProgress = inProgress;
				const response = await cell.action();
				inProgress = inProgress.filter((x) => x !== cell.action);
				return response;
			} catch (err: any) {
				error = err.message || err;
			} finally {
				inProgress = inProgress.filter((x) => x !== cell.action);
			}
		}
		return true;
	}
	function loadOptions(cell: FormField) {
		const cellConfig = Object.assign({}, cell);
		if (cellConfig.dataSource) {
			const fn = new Function(...Object.keys(helpers), `return ${cellConfig.dataSource}`);
			Object.assign(cellConfig, fn(...Object.values(helpers)));
		}
		if (cellConfig.store) {
			let valueField = cellConfig.storeValueField || 'id';
			let nameField = cellConfig.storeNameField || 'attributes.name';
			let disabledField = cellConfig.storeDisabledField || '';
			let options: any[] = [];
			if (cellConfig.store instanceof ApiStore) {
				cellConfig.store.searchValue = cellConfig.searchValue || '';
			}
			cellConfig.store.get().then((data) => {
				options = data.map((item) => {
					return {
						value: get(item, valueField),
						name: String(get(item, nameField)),
						disabled: disabledField && get(item, disabledField),
					};
				});
				cell_runtime[indexOf(cell)].options = options;
				cell_runtime = cell_runtime;
				content = content;
			});
		}
	}
	function comboBoxKeydown(event: KeyboardEvent, cell: FormField) {
		if (event.key === 'Enter') {
			event.preventDefault();
			event.stopPropagation();
			loadOptions(cell);
			return false;
		}
	}
	async function comboBoxSelected(cell: FormField, value: any) {
		if (cell.action) {
			await cell.action(value);
			accessor = accessor;
		}
	}
	onMount(async () => {
		// initialize options
		content.forEach((row) => {
			row.forEach((cell) => {
				if (cell.control === 'ComboBox' && (cell.store || cell.dataSource)) {
					// reset search value
					cell.searchValue = '';
					loadOptions(cell);
				}
			});
		});
		inProgress.splice(0);
	});
	function getName(cell: FormField): string {
		if (cell.name && !cell.getter && !cell.setter) {
			return cell.name;
		}
		const internalIndex = internalValues.findIndex((v: { cell: any }) => v.cell === cell);
		if (internalIndex > -1) {
			return '$' + internalIndex;
		} else {
			internalValues.push({ cell, value: null });
			return '$' + String(internalValues.length - 1);
		}
	}
	// EDIT MODE
	let editConfigForm: FormField[][] = [];
	let editConfigData = {};
	let editConfig = false;
	let editConfigModelAttributes: { name: string; value: string }[] = [];
	let editConfigFormControlProps: FormField[][] = [
		[
			{
				control: 'ComboBox',
				name: 'control',
				label: 'Control',
				options: [
					{ name: 'TextBox', value: 'TextBox' },
					{ name: 'ComboBox', value: 'ComboBox' },
					{ name: 'CheckBox', value: 'CheckBox' },
					{ name: 'Button', value: 'Button' },
					{ name: 'TextArea', value: 'TextArea' },
				],
			},
		],
		[
			{
				control: 'ComboBox',
				name: 'name',
				label: 'Name',
				options: editConfigModelAttributes,
				editable: false,
			},
		],
		[
			{
				control: 'TextBox',
				name: 'label',
				label: 'Label',
			},
		],
		[
			{
				control: 'TextBox',
				name: 'options',
				label: 'Options',
				getter(field: any | undefined) {
					return field && field.map((f: { value: any }) => f.value).join(',');
				},
				setter(field: any | undefined, value: any, data: any) {
					if (data.control !== 'ComboBox') {
						return;
					}
					field = [];
					if (value) {
						field = value.split(',').map((s: string) => {
							return { name: s, value: s };
						});
					}
					return field;
				},
			},
		],
		[
			{
				control: 'TextBox',
				name: 'dataSource',
				label: 'Data source',
			},
		],
		[
			{
				control: 'Button',
				label: 'Remove',
				variant: 'standard',
				async action() {
					const row = content.find((row) => row.find((cell) => cell === editConfigData));
					if (row) {
						const index = row.findIndex((cell) => cell === editConfigData);
						row.splice(index, 1);
						if (row.length === 0) {
							const rowIndex = content.findIndex((r) => r === row);
							content.splice(rowIndex, 1);
						}
						updateConfig();
						editConfig = false;
					}
				},
			},
		],
	];
	let editConfigFormProps: FormField[][] = [
		[
			{
				control: 'TextBox',
				label: 'Model URL',
				name: 'store.config.url',
			},
		],
		[
			{
				control: 'TextBox',
				label: 'Curretn ID',
				name: 'store.config.currentId',
			},
		],
	];

	function editControl(
		event: (MouseEvent & { target: EventTarget & HTMLDivElement }) | any,
		cell: FormField
	) {
		if (!(isEditMode && event.target && event.target.getAttribute('edit-config') === 'control')) {
			return;
		}
		editConfig = true;
		editConfigForm = editConfigFormControlProps;
		editConfigData = cell;
	}
	function addControl(
		event: (MouseEvent & { target: EventTarget & HTMLDivElement }) | any,
		row: FormField[]
	) {
		if (!(isEditMode && event.target && event.target.getAttribute('edit-config') === 'row')) {
			return;
		}
		row.push({
			control: 'TextBox',
			name: 'name',
			label: 'Name',
		});
		content = content;
	}
	function addRowControl() {
		content.push([
			{
				control: 'TextBox',
				name: 'name',
				label: 'Name',
			},
		]);
		updateConfig();
	}
	function updateConfig() {
		content = content;
		if (!configStore) {
			return;
		}
		configStore.save();
	}
	let cell_runtime: { cell: FormField; options: any[] }[] = [];
	function indexOf(cell: FormField): number {
		const index = cell_runtime.findIndex((cov) => cov.cell === cell);
		if (index > -1) {
			return index;
		} else {
			const newCov = { cell, options: cell.options || [] };
			cell_runtime.push(newCov);
			return cell_runtime.length - 1;
		}
	}
	function editFormConfig() {
		editConfig = true;
		editConfigForm = editConfigFormProps;
		editConfigData = {
			store,
		};
	}
	if (store && configStore) {
		configStore
			.getModelMeta(store.config.url)
			.then((model) => {
				const keys = Object.keys(model.attributes);
				editConfigModelAttributes.splice(0);
				editConfigModelAttributes.push({ name: '', value: '' });
				editConfigModelAttributes.push(
					...keys.map((key) => {
						const value = String(get(data, `attributes.${key}`)).substring(0, 10);
						return { name: `attributes.${key} (${value})`, value: `attributes.${key}` };
					})
				);
				editConfigFormControlProps = editConfigFormControlProps;
			})
			.catch(() => {
				editConfigModelAttributes.splice(0);
			});
	}
	$: if (data) {
		accessor = accessor;
	}
</script>

<EditProperties
	form={editConfigForm}
	data={editConfigData}
	bind:open={editConfig}
	on:save={updateConfig}
/>

<form
	class={`${border ? 'section' : ''} ${blur ? 'blur-background' : ''}`}
	style={`margin: ${margin};`}
	on:submit|preventDefault={submit}
>
	{#if isEditMode}
		<Button on:click={editFormConfig}>⚙️</Button>
	{/if}
	{#if title}
		<h2>{__(title)}</h2>
	{/if}
	{#if error}
		<InfoBar open={error != ''} message={__(error)} severity="caution" class="form-error" />
	{/if}
	{#each content as row}
		<div
			class={`form-row ${isEditMode ? 'editable-row' : ''}`}
			on:click={(event) => addControl(event, row)}
			edit-config="row"
		>
			{#each row as cell}
				{#if cell.control === 'TextBox' && cell.name}
					<div
						class={isEditMode ? 'editable' : ''}
						on:click={(event) => editControl(event, cell)}
						edit-config="control"
					>
						<label for={cell.name}>
							{__(cell.label || '')}
						</label>
						<TextBox
							id={cell.name}
							placeholder={cell.placeholder || ''}
							type={cell.type || 'text'}
							clearButton={false}
							bind:value={accessor[getName(cell)]}
						/>
					</div>
				{/if}
				{#if cell.control === 'TextArea' && cell.name}
					<div
						class={isEditMode ? 'editable' : ''}
						on:click={(event) => editControl(event, cell)}
						edit-config="control"
					>
						<label for={cell.name}>
							{__(cell.label || '')}
						</label>
						<TextArea
							id={cell.name}
							placeholder={cell.placeholder || ''}
							rows={cell.rows || 2}
							clearButton={false}
							bind:value={accessor[getName(cell)]}
						/>
					</div>
				{/if}
				{#if cell.control === 'Checkbox'}
					<div
						class={isEditMode ? 'editable' : ''}
						on:click={(event) => editControl(event, cell)}
						edit-config="control"
					>
						<Checkbox id={cell.name} bind:checked={accessor[getName(cell)]}>
							{__(cell.label || '')}
						</Checkbox>
					</div>
				{/if}
				{#if cell.control === 'ComboBox' && cell.name}
					<div
						class={isEditMode ? 'editable' : ''}
						on:click={(event) => editControl(event, cell)}
						edit-config="control"
					>
						<label for={cell.name}>
							{__(cell.label || '')}
						</label>
						<ComboBox
							id={cell.name}
							placeholder={cell.placeholder || ''}
							items={cell_runtime[indexOf(cell)].options}
							class="w-100"
							editable={cell.editable || false}
							bind:searchValue={cell.searchValue}
							bind:value={accessor[getName(cell)]}
							on:keydown={(e) => comboBoxKeydown(e, cell)}
							on:select={() => comboBoxSelected(cell, cell.name && accessor[getName(cell)])}
							on:input={() => {
								if (cell.editable) accessor[getName(cell)] = cell.searchValue;
							}}
						/>
					</div>
				{/if}
				{#if cell.control === 'Button'}
					<Button
						variant={cell.variant || 'standard'}
						on:click={() => buttonAction(cell)}
						disabled={cell.action && inProgress.indexOf(cell.action) > -1}
					>
						{__(cell.label)}
					</Button>
				{/if}
				{#if cell.control === 'Avatar' && cell.name}
					<PersonPicture size={64} src={accessor[getName(cell)]} />
				{/if}
				{#if cell.control === 'Header'}
					<h3>{__(cell.label)}</h3>
				{/if}
				{#if cell.control === 'Empty'}
					<div>&nbsp;</div>
				{/if}
			{/each}
		</div>
	{/each}
	{#if isEditMode}
		<Button variant="hyperlink" on:click={addRowControl}>
			{__('Add Row')}
		</Button>
	{/if}
</form>

<style>
	label {
		margin: 0.2rem 0rem;
		display: block;
	}
	div.form-row {
		margin-top: 1rem;
		display: flex;
		flex-direction: row;
		flex-wrap: nowrap;
		align-items: center;
	}
	div.form-row > * {
		flex-grow: 1;
		width: 1rem;
		margin: 0rem 1rem 0rem 0rem;
		display: inline-block;
	}
	form.section {
		padding: 1rem 1rem;
		border-radius: 2px;
		background-clip: padding-box;
		-webkit-box-shadow: 0 1.6px 3.6px 0 rgb(0 0 0 / 13%), 0 0.3px 0.9px 0 rgb(0 0 0 / 11%);
		box-shadow: 0 1.6px 3.6px 0 rgb(0 0 0 / 13%), 0 0.3px 0.9px 0 rgb(0 0 0 / 11%);
	}
	.blur-background {
		background-color: rgba(255, 255, 255, 0.3);
		backdrop-filter: blur(5px);
		-webkit-backdrop-filter: blur(5px);
	}
	h2 {
		margin: 0px 0px 0.5rem 0px;
		padding: 0px;
	}
	:global(.w-100) {
		width: 100%;
	}
	:global(.form-error) {
		position: sticky !important;
		top: 0px;
		z-index: 1;
	}
	div.editable {
		cursor: crosshair !important;
		position: relative;
	}
	div.editable::after {
		content: '⚙️';
		position: absolute;
		bottom: 0rem;
		right: 0rem;
	}
	div.editable-row {
		cursor: crosshair !important;
		position: relative;
	}
	div.editable-row::after {
		content: '➕';
		position: absolute;
		bottom: 0rem;
		right: 0rem;
	}
</style>
