<script lang="ts">
	import Login from '../stories/Login.svelte';
	import { login } from '../store';
	import { onMount } from 'svelte';

	onMount(() => {
		login.set(null);
	});
</script>

<Login />
