<script lang="ts">
	import type FormField from '$lib/FormField';
	import Form from '../../stories/Form.svelte';
	import type StoreInterface from '$lib/StoreInterface';
	import ApiStore from '$lib/ApiStore';
	import { login } from '../../store';

	let form: FormField[][] = [
		[
			{
				control: 'Avatar',
				name: 'avatar',
			},
		],
		[
			{
				control: 'Header',
				label: 'Account Settings',
			},
		],
		[
			{
				control: 'TextBox',
				type: 'text',
				name: 'attributes.name',
				label: 'Name',
			},
		],
		[
			{
				control: 'Button',
				variant: 'accent',
				label: 'Update Information',
				action: () => {
					return store.update(data.id, {
						attributes: {
							name: data.attributes.name,
						},
					});
				},
			},
		],
		[
			{
				control: 'Header',
				label: 'Change Password',
			},
		],
		[
			{
				control: 'TextBox',
				type: 'password',
				name: 'password',
				label: 'New Password',
			},
		],
		[
			{
				control: 'TextBox',
				type: 'password',
				name: 'confirm_password',
				label: 'Confirm Password',
			},
		],
		[
			{
				control: 'Button',
				variant: 'accent',
				label: 'Change Password',
				action: () => {
					if (data.password !== data.confirm_password) {
						return Promise.reject('Passwords do not match');
					}
					if (!data.password) {
						return Promise.reject('Password is required');
					}
					return store.update(data.id, {
						attributes: {
							password: data.password,
						},
					});
				},
			},
		],
	];
	let store: StoreInterface = new ApiStore({
		url: 'users',
		root: 'data',
	});
	let data: {
		id: string;
		attributes: {
			name: string;
		};
		password?: string;
		confirm_password?: string;
	} = {
		id: '',
		attributes: {
			name: '',
		},
		password: '',
		confirm_password: '',
	};
	login.subscribe(async (login: { attributes: { user_id: string } } | null) => {
		if (login) {
			const record = await store.show(login.attributes.user_id);
			data = Object.assign(data, record);
			form = form;
		}
	});
</script>

<Form content={form} border={false} {data} />
