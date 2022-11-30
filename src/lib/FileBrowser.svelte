<script lang="ts">
	import Icon from './Icon.svelte';

	// Filebrowser component
	const files = [
		{
			name: 'reportes',
			type: 'file',
			size: 123456,
			modified: 1234567890,
			icon: 'reportes_auditoria',
		},
	];
</script>

<div class="file-browser">
	{#each files as file}
		<div class="file">
			<Icon icon={file.icon} />
			<span>{file.name}</span>
		</div>
	{/each}
	<div class="text-box-underline" />
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
        padding: 1rem;
        cursor: default;
	}
	.text-box-underline {
		block-size: calc(100% + 2px);
		border-radius: var(--fds-control-corner-radius);
		inline-size: calc(100% + 2px);
		inset-block-start: -1px;
		inset-inline-start: -1px;
		overflow: hidden;
		pointer-events: none;
		position: absolute;
	}
	.text-box-underline:after {
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
	}
    .file span {
        font-size: 1rem;
    }
    .file:hover {
        background-color: var(--control-alt-fill-tertiary);
    }
</style>
