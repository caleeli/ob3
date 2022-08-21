<script lang="ts">
	import { login } from '../store';
	import ApiStore from '../lib/ApiStore';
	import type FormField from '../lib/FormField';
	import { translation as __ } from '../lib/translations';
	import Form from './Form.svelte';
	import { goto } from '$app/navigation';

	let title = __('Sign In');
	let content: FormField[][] = [
		[
			{
				control: 'TextBox',
				type: 'text',
				name: 'username',
				placeholder: __('Username')
			}
		],
		[
			{
				control: 'TextBox',
				type: 'password',
				name: 'password',
				placeholder: __('Password')
			}
		],
		[
			{
				control: 'Button',
				variant: 'accent',
				label: __('Login')
			}
		]
	];
	let blur = true;
	let data = {
		username: '',
		password: ''
	};
	let api = new ApiStore({
		url: 'http://localhost/projects/callizaya2/public/api.php/ob3/login',
		root: 'data'
	});
	let error = '';
	async function submit(data: { detail: { username: any; password: any } }) {
		try {
			const token = await api.create({
				attributes: {
					username: data.detail.username,
					password: data.detail.password
				}
			});
			login.set(token);
			goto('/dashboard');
		} catch (err: any) {
			error = err.message;
		}
	}
</script>

<div class="bg">
	<div class="panel">
		<Form {title} {content} {blur} {data} on:submit={submit} {error} />
	</div>
</div>

<style>
	div.bg {
		background-image: url('./images/login-background.jpg');
		background-size: cover;
		display: flex;
		align-items: center;
		justify-content: center;
		height: calc(100vh - 4rem);
	}
	div.panel {
		width: 25rem;
	}
</style>
