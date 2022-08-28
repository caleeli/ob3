<script lang="ts">
	import { Button, Checkbox, InfoBar, ProgressRing } from 'fluent-svelte';
	import { TextBox } from 'fluent-svelte';
	import TextArea from '../lib/TextArea.svelte';
	import { PersonPicture } from 'fluent-svelte';
	import { ComboBox } from 'fluent-svelte';
	import { get, set } from 'lodash';
	import type FormField from '../lib/FormField';
	import { translation as __ } from '../lib/translations';
	import { createEventDispatcher } from 'svelte';
	import ApiStore from '../lib/ApiStore';
	import { onMount } from 'svelte';

	const dispatch = createEventDispatcher();

	export let title: string = '';
	export let content: FormField[][] = [];
	export let border: boolean = true;
	export let blur: boolean = false;
	export let data: any = {};
	export let error = '';
	let inProgress: any[] = [];
	let internalValues: { cell: FormField; value: any }[] = [];
	let accessor = new Proxy(data, {
		get: (target, name) => {
			if (typeof name === 'string' && name.substring(0, 1) === '$') {
				const index = parseInt(name.substring(1));
				const cell = internalValues[index].cell;
				const field = cell.name ? get(target, cell.name) : internalValues[index].value;
				if (cell.getter) {
					return cell.getter(field);
				} else {
					return field;
				}
			} else {
				return get(target, name);
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
						const result = cell.setter(field, value);
						if (result && cell.name) {
							set(target, cell.name, result);
						}
					}
				} else {
					throw new Error(`Invalid internal value index: ${index}`);
				}
				return target;
			} else {
				return set(target, name, value);
			}
		},
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
				console.log(inProgress);
			}
		}
		return true;
	}
	function loadOptions(cell: FormField) {
		if (cell.store) {
			let valueField = cell.storeValueField || 'id';
			let nameField = cell.storeNameField || 'attributes.name';
			let disabledField = cell.storeDisabledField || '';
			cell.options = [];
			if (cell.store instanceof ApiStore) {
				cell.store.searchValue = cell.searchValue || '';
			}
			cell.store.get().then((data) => {
				cell.options = data.map((item) => {
					return {
						value: get(item, valueField),
						name: String(get(item, nameField)),
						disabled: disabledField && get(item, disabledField),
					};
				});
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
				if (cell.control === 'ComboBox' && cell.store) {
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
</script>

<form
	class={`${border ? 'section' : ''} ${blur ? 'blur-background' : ''}`}
	on:submit|preventDefault={submit}
>
	<h2>{__(title)}</h2>
	{#if error}
		<InfoBar open={error != ''} message={__(error)} severity="caution" class="form-error" />
	{/if}
	{#each content as row}
		<div class="form-row">
			{#each row as cell}
				{#if cell.control === 'TextBox' && cell.name}
					<div>
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
					<div>
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
					<div>
						<Checkbox id={cell.name} bind:checked={accessor[getName(cell)]}>
							{__(cell.label || '')}
						</Checkbox>
					</div>
				{/if}
				{#if cell.control === 'ComboBox' && cell.options && cell.name}
					<div>
						<label for={cell.name}>
							{__(cell.label || '')}
						</label>
						<ComboBox
							id={cell.name}
							placeholder={cell.placeholder || ''}
							items={cell.options}
							class="w-100"
							editable={true}
							bind:searchValue={cell.searchValue}
							bind:value={accessor[getName(cell)]}
							on:keydown={(e) => comboBoxKeydown(e, cell)}
							on:select={() => comboBoxSelected(cell, cell.name && accessor[getName(cell)])}
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
</style>
