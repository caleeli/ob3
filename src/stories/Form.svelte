<script lang="ts">
	import { Button } from 'fluent-svelte';
	import { TextBox } from 'fluent-svelte';
	import { PersonPicture } from 'fluent-svelte';
	import { ComboBox } from 'fluent-svelte';

	export let title: string = '';
	export let content: {
		control: string;
		type:
			| 'number'
			| 'search'
			| 'text'
			| 'password'
			| 'email'
			| 'tel'
			| 'url'
			| 'date'
			| 'datetime-local'
			| 'month'
			| 'time'
			| 'week';
		name: string;
		label: string;
		placeholder: string;
		variant: 'standard' | 'accent' | 'hyperlink';
		options: {
			value: string;
			label: string;
		}[];
	}[][] = [];
	export let blur: boolean = false;
	export let data: any = {};
</script>

<form class={`section ${blur ? 'blur-background' : ''}`}>
	<h2>{title}</h2>
	{#each content as row}
		<div class="wrapper">
			{#each row as cell}
				{#if cell.control === 'TextBox'}
					<div>
						<label for={cell.name}>
							{cell.label || ''}
						</label>
						<TextBox
							id={cell.name}
							placeholder={cell.placeholder || ''}
							type={cell.type}
							bind:value={data[cell.name]}
						/>
					</div>
				{/if}
				{#if cell.control === 'ComboBox'}
					<div>
						<label for={cell.name}>
							{cell.label || ''}
						</label>
						<ComboBox
							id={cell.name}
							placeholder={cell.placeholder || ''}
							items={cell.options}
							class="w-100"
							bind:value={data[cell.name]}
						/>
					</div>
				{/if}
				{#if cell.control === 'Button'}
					<Button variant={cell.variant}>
						{cell.label}
					</Button>
				{/if}
				{#if cell.control === 'Avatar'}
					<PersonPicture size={64} src={data[cell.name]} />
				{/if}
				{#if cell.control === 'Header'}
					<h3>{cell.label}</h3>
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
	div.wrapper {
		margin-top: 1rem;
		display: flex;
		flex-direction: row;
		flex-wrap: nowrap;
		align-items: center;
	}
	div.wrapper > * {
		flex-grow: 1;
		margin: 0rem 1rem 0rem 0rem;
		display: inline-block;
	}
	form.section {
		background: #ffffff;
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
</style>
