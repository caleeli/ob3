
class GridConfig {
	public headers: {
		label: string;
		value: string;
		sortBy?: string;
		align: 'left' | 'right' | 'center';
		control?: 'text' | 'select' | 'checkbox' | 'radio' | 'date' | 'time' | 'datetime' | 'actions';
		width?: string;
		format?: string[];
		class?: string;
		groupRows?: boolean;
	}[] = [];
	public sort: {
		field: string;
		order: 'asc' | 'desc';
	} = {
			field: '',
			order: 'asc',
		};
}

export default GridConfig;
