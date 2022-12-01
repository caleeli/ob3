<script lang="ts">
	import { Button } from 'fluent-svelte';
	import Icon from './Icon.svelte';

	export let value = '';
	export let rows = 10;

	// Filebrowser component
	const files = [
		{
			name: 'cronograma.docx in ' + value,
			url: 'cronograma.docx',
			type: 'file',
			size: 123456,
			modified: 1234567890,
			icon: 'docx',
		},
		{
			name: 'muestra.xlsx',
			url: 'muestra.xlsx',
			type: 'file',
			size: 123456,
			modified: 1234567890,
			icon: 'xlsx',
		},
		{
			name: 'report.pdf',
			url: 'report.pdf',
			type: 'file',
			size: 123456,
			modified: 1234567890,
			icon: 'pdf',
		},
		{
			name: 'cronograma.docx',
			url: 'cronograma.docx',
			type: 'file',
			size: 123456,
			modified: 1234567890,
			icon: 'docx',
		},
		{
			name: 'muestra.xlsx',
			url: 'muestra.xlsx',
			type: 'file',
			size: 123456,
			modified: 1234567890,
			icon: 'xlsx',
		},
		{
			name: 'file.txt',
			url: 'file.txt',
			type: 'file',
			size: 123456,
			modified: 1234567890,
			icon: 'file',
		},
		{
			name: 'cronograma.docx',
			url: 'cronograma.docx',
			type: 'file',
			size: 123456,
			modified: 1234567890,
			icon: 'docx',
		},
		{
			name: 'muestra.xlsx',
			url: 'muestra.xlsx',
			type: 'file',
			size: 123456,
			modified: 1234567890,
			icon: 'xlsx',
		},
		{
			name: 'file.txt',
			url: 'file.txt',
			type: 'file',
			size: 123456,
			modified: 1234567890,
			icon: 'file',
		},
		{
			name: 'cronograma.docx',
			url: 'cronograma.docx',
			type: 'file',
			size: 123456,
			modified: 1234567890,
			icon: 'docx',
		},
		{
			name: 'muestra.xlsx',
			url: 'muestra.xlsx',
			type: 'file',
			size: 123456,
			modified: 1234567890,
			icon: 'xlsx',
		},
		{
			name: 'file.txt',
			url: 'file.txt',
			type: 'file',
			size: 123456,
			modified: 1234567890,
			icon: 'file',
		},
	];
</script>

<div class="file-browser">
	<div class="file-browser-container" style={`--rows: ${rows * 20 + 6 - 34}px;`}>
		{#each files as file}
			<a class="file" href={file.url} title={file.name} download>
				<Icon icon={file.icon} />
				<span>{file.name}</span>
			</a>
		{/each}
	</div>
	<div class="file-browser-underline" />
</div>

<style>
	.file-browser {
		background-clip: padding-box;
		background-color: var(--fds-control-fill-default);
		border: 1px solid var(--fds-control-stroke-default);
		border-radius: var(--fds-control-corner-radius);
		cursor: text;
		inline-size: 100%;
		position: relative;
		cursor: default;
	}
	.file-browser-container {
		padding: 1rem;
		overflow-x: hidden;
		overflow-y: auto;
		height: var(--rows);
	}
	.file-browser-underline {
		block-size: calc(100% + 2px);
		border-radius: var(--fds-control-corner-radius);
		inline-size: calc(100% + 2px);
		inset-block-start: -1px;
		inset-inline-start: -1px;
		overflow: hidden;
		pointer-events: none;
		position: absolute;
	}
	.file-browser-underline:after {
		block-size: 100%;
		border-bottom: 1px solid var(--fds-control-strong-stroke-default);
		box-sizing: border-box;
		content: '';
		inline-size: 100%;
		inset-block-end: 0;
		inset-inline-start: 0;
		position: absolute;
	}
	.file {
		display: inline-flex;
		align-items: center;
		flex-direction: column;
		font-size: 4rem;
		cursor: pointer;
		padding: 0.5rem;
		border-radius: 4px;
		-webkit-user-select: none; /* Safari */
		-moz-user-select: none; /* Firefox */
		-ms-user-select: none; /* IE10+/Edge */
		user-select: none; /* Standard */
		width: 6rem;
		color: var(--fds-accent-text-primary);
		text-decoration: none;
	}
	.file span {
		font-size: 1rem;
		/* text ellipsis */
		overflow: hidden;
		width: 100%;
		text-align: center;
		word-wrap: break-word;
	}
	.file:hover {
		background-color: var(--control-alt-fill-tertiary);
	}
</style>
