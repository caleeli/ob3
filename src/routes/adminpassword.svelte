<script lang="ts">
	import type FormField from '../lib/FormField';
	import Form from '../stories/Form.svelte';
	import { translation as __ } from '../lib/translations';

	let form: FormField[][] = [
		[
			{
				control: 'Avatar',
				name: 'avatar'
			}
		],
		[
			{
				control: 'Header',
				label: 'Account Settings'
			}
		],
		[
			{
				control: 'TextBox',
				type: 'text',
				name: 'firstname',
				label: 'First Name'
			},
			{
				control: 'TextBox',
				type: 'text',
				name: 'lastname',
				label: 'Last Name'
			}
		],
		[
			{
				control: 'TextBox',
				type: 'text',
				name: 'email',
				label: 'E-mail'
			}
		],
		[
			{
				control: 'Button',
				variant: 'accent',
				label: 'Update Information'
			}
		],
		[
			{
				control: 'Header',
				label: 'Change Password'
			}
		],
		[
			{
				control: 'TextBox',
				type: 'password',
				name: 'password',
				label: 'New Password'
			}
		],
		[
			{
				control: 'TextBox',
				type: 'password',
				name: 'confirm_password',
				label: 'Confirm Password'
			}
		],
		[
			{
				control: 'Button',
				variant: 'accent',
				label: 'Change Password'
			}
		]
	];
</script>

<Form content={form} border={false} />
