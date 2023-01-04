import { edit_mode } from '../store';
import type ApiStore from './ApiStore';
import type FormField from './FormField';
const adminer_url = import.meta.env.VITE_ADMINER_URL;

export function onConfigModeChange(cb: (arg0: boolean) => void) {
	edit_mode.subscribe((edit_mode) => {
		cb(edit_mode);
	});
}

export function addModelConfig(form: FormField[][], store: ApiStore) {
	form.push([
		{
			control: 'TextBox',
			label: 'Model URL',
			name: 'store.config.url',
		},
		{
			control: 'Button',
			label: 'Adminer',
			variant: 'hyperlink',
			async action() {
				const model = store.config.url;
				const url = `${adminer_url}&model=${model}&name=${model}`;
				window.open(url, '_blank');
			},
		},
	]);
}