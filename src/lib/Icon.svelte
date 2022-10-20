<script lang="ts">
	import icons from '../lib/icons';

	export let icon: string;
	export let color: string;

	let fluent_icons: { [key: string]: string } = {
		undefined: 'home',
		'': 'home',
		folder: 'folder',
		file: 'file',
		component: 'cube',
		building: 'box-open',
		processor: 'microchip',
		call_processor: 'download',
		import: 'file-import',
	};

	const svg_icon = icons.find((ic) => ic.name === icon);
</script>

{#if svg_icon}
	<svelte:component this={svg_icon.component} />
{:else}
	<i
		class={`icon icon-ic_fluent_${fluent_icons[icon] || icon}_20_regular`}
		style={`color: ${color};`}
	/>
{/if}
