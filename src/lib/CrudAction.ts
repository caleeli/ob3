
class CrudAction {
	public icon: string;
	public label?: string;
	public action: string | (() => void) | undefined;
	public initial?: any;
	public form: any[][] = [];
}

export default CrudAction;
