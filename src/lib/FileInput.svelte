<script lang="ts">
	import { translation as __ } from '../lib/translations';
	import Icon from './Icon.svelte';
	export let name = '';
	export let placeholder = '';
	export let label = '';
	let hoverButton = false;

	function hover(
		event:
			| {
					clientX: any;
					target: { getBoundingClientRect: () => { (): any; new (): any; right: number } };
			  }
			| any
	) {
		// check if x is over button, if so, highlight button
		// if not, unhighlight button
		const x = event.clientX;
		const buttonWidth = 28;
		const buttonX = event.target.getBoundingClientRect().right - buttonWidth;
		if (x >= buttonX && !hoverButton) {
			hoverButton = true;
		}
		if (x < buttonX && hoverButton) {
			hoverButton = false;
		}
	}
</script>

<div>
	<label for={name}>{label}</label>
	<div class={`text-box-container ${hoverButton ? 'hover' : ''}`}>
		<input
			class="text-box text-box-file"
			{name}
			{placeholder}
			type="file"
			on:mousemove={hover}
			on:mouseleave={() => (hoverButton = false)}
		/>
		<input class="text-box" {placeholder} type="text" />
		<div class="text-box-underline" />
		<div class="text-box-buttons">
			<button class="text-box-button" type="button" aria-label={__('Browse')}>
				<Icon icon="folder" />
			</button>
			<!--<TextBoxButton>-->
		</div>
	</div>
	<!--<TextBox>-->
</div>

<style>
	.text-box-container {
		align-items: center;
		background-clip: padding-box;
		background-color: var(--fds-control-fill-default);
		border: 1px solid var(--fds-control-stroke-default);
		border-radius: var(--fds-control-corner-radius);
		cursor: text;
		inline-size: 100%;
		position: relative;
		display: flex;
	}
	.text-box-container input[type='file'] {
		position: absolute;
	}
	.text-box-container input {
		background-color: transparent;
		border: none;
		border-radius: var(--fds-control-corner-radius);
		box-sizing: border-box;
		color: var(--fds-text-primary);
		cursor: unset;
		flex: 1 1 auto;
		font-family: var(--fds-font-family-text);
		font-size: var(--fds-body-font-size);
		font-weight: 400;
		inline-size: 100%;
		line-height: 20px;
		margin: 0;
		min-block-size: 30px;
		outline: none;
		padding-inline: 10px;
		-webkit-user-select: none;
		-moz-user-select: none;
		-ms-user-select: none;
		user-select: none;
		resize: none;
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
	.text-box-container:focus-within .text-box-underline:after {
		border-bottom: 2px solid var(--fds-accent-default);
	}
	.text-box-buttons {
		align-items: center;
		cursor: default;
		display: flex;
		flex: 0 0 auto;
	}
	.text-box-button {
		align-items: center;
		background-color: var(--fds-subtle-fill-transparent);
		border: none;
		border-radius: var(--fds-control-corner-radius);
		box-sizing: border-box;
		color: var(--fds-text-secondary);
		display: flex;
		justify-content: center;
		min-block-size: 22px;
		min-inline-size: 26px;
		outline: none;
		padding: 3px 5px;
	}
	.text-box-buttons > .text-box-button:last-of-type {
		-webkit-margin-end: 4px;
		margin-inline-end: 4px;
	}
	.text-box-button:hover {
		background-color: var(--fds-subtle-fill-secondary);
	}
	.text-box-container.hover .text-box-button {
		background-color: var(--fds-subtle-fill-secondary);
	}
	.text-box-container.hover {
		cursor: default;
	}
	.text-box-container:hover {
		background-color: var(--fds-control-fill-secondary);
	}
	.text-box-file {
		opacity: 0;
	}
</style>
