class GridConfig {
	public headers: {
		label: string;
		value: string;
		field?: string;
		sortable?: boolean;
		sorted?: 'asc' | 'desc';
		align: 'left' | 'right' | 'center';
		control?: 'text' | 'select' | 'checkbox' | 'radio' | 'date' | 'time' | 'datetime' | 'actions';
		width?: string;
		format?: string[];
		class?: string;
		groupRows?: boolean;
	}[] = [];
	public sort?: {
		field: string;
		direction: 'asc' | 'desc';
	}[] = [];
	public multiSelect? = false;
	public onselect?: string;

	// Constructor
	constructor(config: any) {
		this.headers = config.headers;
		this.sort = config.sort;
		this.multiSelect = config.multiSelect;
		this.onselect = config.onselect;
	}
}

export default GridConfig;
