<script lang="ts">
	import Form from 'src/stories/Form.svelte';
	import FormParser from './FormParser';

	export let config: any;
	export let level: number = 0;
</script>

{#if level % 2 === 0 && config instanceof Array}
	{#each config as item}
		<div class="row">
			<svelte:self config={item} level={level + 1} />
		</div>
	{/each}
{:else if level % 2 === 1 && config instanceof Array}
	{#each config as item}
		<div class="cell">
			<svelte:self config={item} level={level + 1} />
		</div>
	{/each}
{:else if config instanceof Object && config.component === 'Form'}
	<Form
		title={config.title}
		content={FormParser.content(config.content)}
		border={config.border}
		blur={config.blur}
		data={FormParser.data(config.data)}
		store={FormParser.store(config.store)}
		configStore={FormParser.configStore(config.configStore)}
		margin={config.margin}
	/>
{/if}

<style scoped>
	.row {
		display: flex;
		flex-direction: row;
	}
	.cell {
		display: flex;
		flex-direction: column;
	}
</style>
