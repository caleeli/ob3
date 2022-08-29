
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
	public sort: {
		field: string;
		direction: 'asc' | 'desc';
	}[] = [];
	public multiSelect?= false;
}

export default GridConfig;
