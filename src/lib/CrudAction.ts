import type FormField from "./FormField";

class CrudAction {
	public icon = 'add';
	public label?: string;
	public action?: string | (() => void) | undefined;
	public initial?: any;
	public form?: FormField[][];
}

export default CrudAction;
