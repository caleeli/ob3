<script lang="ts">
	import { createEventDispatcher } from 'svelte';
	import { get_current_component } from 'svelte/internal';
	// import { externalMouseEvents, createEventForwarder } from '../lib/internal';

	import TextBoxButton from 'fluent-svelte/TextBox/TextBoxButton.svelte';

	type TextInputTypes =
		| 'text'
		| 'number'
		| 'search'
		| 'password'
		| 'email'
		| 'tel'
		| 'url'
		| 'date'
		| 'datetime-local'
		| 'month'
		| 'time'
		| 'week';

	/** The input's current value. */
	export let value: any = '';

	/** Determiness the input type of the textbox. */
	export let type: TextInputTypes = 'text';

	/** The initial placeholder text displayed if no value is present. */
	export let placeholder: string = undefined;

	/** Determines whether a text clear button is present. */
	export let clearButton = true;

	/** Determines whether a search button is present when `type` is set to "search". */
	export let searchButton = true;

	/** Determines whether a password reveal button is present when `type` is set to "password". */
	export let revealButton = true;

	/** Determines whether the textbox can be typed in or not. */
	export let readonly = false;

	/** Controls whether the TextBox is intended for user interaction, and styles it accordingly. */
	export let disabled = false;

	/** Specifies a custom class name for the TextBox container. */
	let className = '';
	export { className as class };

	/** Obtains a bound DOM reference to the TextBox's input element. */
	export let inputElement: HTMLInputElement = null;

	/** Obtains a bound DOM reference to the TextBox's container element. */
	export let containerElement: HTMLDivElement = null;

	/** Obtains a bound DOM reference to the TextBox's buttons container element. */
	export let buttonsContainerElement: HTMLDivElement = null;

	/** Obtains a bound DOM reference to the TextBox's clear button element. Only available if `clearButton` is set to true, `readonly` is set to false, and a `value` is present. */
	export let clearButtonElement: HTMLButtonElement = null;

	/** Obtains a bound DOM reference to the TextBox's search button element. Only available if `type` is set to `search`. */
	export let searchButtonElement: HTMLButtonElement = null;

	/** Obtains a bound DOM reference to the TextBox's reveal button element. Only available if `type` is set to `password`. */
	export let revealButtonElement: HTMLButtonElement = null;

	const dispatch = createEventDispatcher();

	function handleClear(event) {
		dispatch('clear', event);
		inputElement.focus();
		value = '';
	}

	function handleSearch(event) {
		dispatch('search', event);
		inputElement.focus();
	}

	function handleReveal(event) {
		inputElement.focus();
		inputElement.setAttribute('type', 'text');
		dispatch('reveal', event);
		let revealButtonMouseDown = true;
		const hidePassword = () => {
			if (!revealButtonMouseDown) return;
			inputElement.focus();
			revealButtonMouseDown = false;
			inputElement.setAttribute('type', 'password');
			window.removeEventListener('mouseup', hidePassword);
		};
		window.addEventListener('mouseup', hidePassword);
	}

	const inputProps = {
		class: 'text-area',
		disabled: disabled || undefined,
		readonly: readonly || undefined,
		placeholder: placeholder || undefined,
		...$$restProps
	};
</script>

<!--
@component
The TextBox control lets a user type text into an app. The text displays on the screen in a simple, plaintext format on a single line. It additionally comes with a set of buttons which allow users to perform specialized actions on the text, such as showing a password or clearing the TextBox's contents. [Docs](https://fluent-svelte.vercel.app/docs/components/texbox)
- Usage:
    ```tsx
    <TextBox placeholder="Enter your name." />
    ```
-->
<div class="text-box-container {className}" class:disabled bind:this={containerElement}>
	<!-- Dirty workaround for the fact that svelte can't handle two-way binding when the input type is dynamic. -->
	<!-- prettier-ignore -->
	<textarea bind:value={value} bind:this={inputElement} {...inputProps}></textarea>
	<div class="text-box-underline" />
	<div class="text-box-buttons" bind:this={buttonsContainerElement}>
		{#if !disabled}
			{#if clearButton && value && !readonly}
				<TextBoxButton
					class="text-box-clear-button"
					aria-label="Clear value"
					on:click={handleClear}
					bind:element={clearButtonElement}
				>
					<svg
						aria-hidden="true"
						xmlns="http://www.w3.org/2000/svg"
						width="12"
						height="12"
						viewBox="0 0 12 12"
					>
						<path
							fill="currentColor"
							d="M2.08859 2.21569L2.14645 2.14645C2.32001 1.97288 2.58944 1.9536 2.78431 2.08859L2.85355 2.14645L6 5.293L9.14645 2.14645C9.34171 1.95118 9.65829 1.95118 9.85355 2.14645C10.0488 2.34171 10.0488 2.65829 9.85355 2.85355L6.707 6L9.85355 9.14645C10.0271 9.32001 10.0464 9.58944 9.91141 9.78431L9.85355 9.85355C9.67999 10.0271 9.41056 10.0464 9.21569 9.91141L9.14645 9.85355L6 6.707L2.85355 9.85355C2.65829 10.0488 2.34171 10.0488 2.14645 9.85355C1.95118 9.65829 1.95118 9.34171 2.14645 9.14645L5.293 6L2.14645 2.85355C1.97288 2.67999 1.9536 2.41056 2.08859 2.21569L2.14645 2.14645L2.08859 2.21569Z"
						/>
					</svg>
				</TextBoxButton>
			{/if}
		{/if}
		<slot name="buttons" {value} />
	</div>
	<slot />
</div>

<style>
	.text-box-container {
		align-items: center;
		background-clip: padding-box;
		background-color: var(--fds-control-fill-default);
		border: 1px solid var(--fds-control-stroke-default);
		border-radius: var(--fds-control-corner-radius);
		cursor: text;
		display: flex;
		inline-size: 100%;
		position: relative;
	}
	.text-area {
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
</style>
