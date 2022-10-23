import type FormField from './FormField';

class CrudAction {
	public icon? = 'add';
	public label?: string;
	public href?: string | undefined;
	public action?: string | (() => void) | undefined;
	public initial?: any;
	public form?: FormField[][];
	public handler?: (record: any, selected: any[]) => Promise<void>;
}

export default CrudAction;
