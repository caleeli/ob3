import ApiStore from '../lib/ApiStore';
import FileInput from '../lib/FileInput.svelte';

export default {
	title: 'Example/FileInput',
	component: FileInput,
	argTypes: {
		label: { control: 'text' },
		name: { control: 'text' },
		placeholder: { control: 'text' },
	},
};

// More on component templates: https://storybook.js.org/docs/svelte/writing-stories/introduction#using-args
const Template = (args) => ({
	Component: FileInput,
	props: args,
	on: {
	},
});

// More on args: https://storybook.js.org/docs/svelte/writing-stories/args
export const SimpleUpload = Template.bind({});
SimpleUpload.args = {
	label: 'Upload file',
	name: 'upload',
	placeholder: 'Select a file',
	store: new ApiStore({
		url: 'upload',
	}),
};
