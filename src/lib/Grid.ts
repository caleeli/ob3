import type GridConfig from "./GridConfig";
import type StoreInterface from "./StoreInterface";

class Grid {
	private config: GridConfig;
	public cell: any[][] = [];
	public collapsed: any[][] = [];
	public data: any[] = [];
	public store: StoreInterface;
	public meta: { query: string; params: any } = { query: "", params: {} };
	public error = "";
	public sortBy: { field: string, direction: 'asc' | 'desc' }[] = [];
	// Constructor
	constructor(config: GridConfig, store: StoreInterface) {
		this.config = config;
		this.store = store;
		// Initialize sort columns
		this.sortBy = this.config.headers.filter((header: any) => header.groupRows || header.sorted)
			.map((header: any) => ({ field: header.field, direction: header.sorted || 'asc' }));
		this.config.sort.forEach((sortConfig: {
			field: string;
			direction: 'asc' | 'desc';
		}) => {
			const col = this.sortBy.find((col): boolean => col.field === sortConfig.field);
			if (col) {
				col.direction = sortConfig.direction;
			} else {
				this.sortBy.push({ field: sortConfig.field, direction: sortConfig.direction });
			}
		});
		store.setSort(this.sortBy);
	}

	public async toggleSort(field: string) {
		const isGrouped = this.config.headers.find((header: any) => header.groupRows && header.field === field);
		const col = this.sortBy.find((col): boolean => col.field === field);
		if (!col) {
			this.sortBy.push({ field, direction: 'asc' });
		} else if (!isGrouped && col.direction === 'desc') {
			this.sortBy.splice(this.sortBy.indexOf(col), 1);
		} else {
			col.direction = col.direction === 'asc' ? 'desc' : 'asc';
		}
		const data = await this.store.sort(this.sortBy);
		this.cleanCells();
		this.hydrate(data);
	}

	public getDirection(field: string | undefined): "asc" | "desc" | null {
		const col = this.sortBy.find((col): boolean => col.field === field);
		if (!col) {
			return null;
		}
		return col.direction;
	}

	public async load() {
		try {
			const data = await this.store.get(0);
			this.meta = this.store.getMeta();
			this.cleanCells();
			this.hydrate(data);
		} catch (error) {
			this.error = String(error);
		}
	}

	public async loadFromData(data: any[]) {
		this.data.splice(0);
		this.cleanCells();
		this.hydrate(data);
	}

	private cleanCells() {
		this.cell.splice(0);
		this.collapsed.splice(0);
	}

	public async loadNextPage() {
		const data = await this.store.getNextPage();
		this.meta = this.store.getMeta();
		this.hydrate(data);
	}

	private hydrate(data: any[]) {
		try {
			this.data.push(...data);
			data.forEach((dataRow: any) => {
				const rowCell: any[] = [];
				this.config.headers.forEach((header: any) => {
					const value = this.calcValue(header, dataRow);
					rowCell.push(value);
				});
				this.cell.push(rowCell);
			});
		} catch (error) {
			this.error = String(error);
			console.error(error);
		}

	}

	public formatted(row: number, col: number) {
		let value = this.cell[row][col];
		if (value === undefined || value === null) {
			return "";
		}
		if (!this.config || !this.config.headers || !this.config.headers[col] || !this.config.headers[col].format) {
			return value;
		}
		const formatters: { [functionName: string]: ((value: any) => string) } = {
			"currency": this.currency,
		};
		this.config.headers[col].format?.forEach((format: string | ((value: any) => string)) => {
			if (typeof format === "string" && formatters[format]) {
				value = formatters[format](value);
			} else if (typeof format === "function") {
				value = format(value);
			} else {
				throw new Error(`Unknown format: ${format}`);
			}
		});
		return value;
	}

	private calcValue(header: any, dataRow: any): string {
		const properties = Object.keys(dataRow);
		return (new Function(...properties, 'return ' + header.value)).apply(this, Object.values(dataRow));
	}

	public firstInGroup(row: number, col: number): boolean {
		if (!this.config.headers[col].groupRows) {
			return true;
		}
		if (row === 0) {
			return true;
		}
		return this.cell[row][col] !== this.cell[row - 1][col];
	}
	// Calculate rowspan of cell
	public rowspan(row: number, col: number): number {
		if (!this.config.headers[col].groupRows) {
			return 1;
		}
		const rowsCount = this.cell.length;
		let rowspan = 0;
		const value = this.cell[row][col];
		for (; row < rowsCount; row++) {
			if (this.cell[row][col] === value) {
				rowspan++;
			} else {
				break;
			}
		}
		return rowspan;
	}
	// Calculate colspan of cell
	public colspan(row: number, col: number): number {
		if (!this.config.headers[col].groupRows) {
			return 1;
		}
		const colsCount = this.cell[row].length;
		let colspan = -1;
		const value = this.cell[row][col];
		for (; col < colsCount; col++) {
			if (this.cell[row][col] === value) {
				colspan++;
			}
		}
		return colspan;
	}

	public getRowData(row: number) {
		return this.data[row];
	}

	public currency(number: number | bigint) {
		return new Intl.NumberFormat('es-BO', {
			minimumFractionDigits: 2
		}).format(number);
	}

	public collapse(col: number, value: any) {
		if (!this.collapsed[col]) {
			this.collapsed[col] = [];
		}
		if (this.collapsed[col].includes(value)) {
			this.collapsed[col] = this.collapsed[col].filter((v) => v !== value);
		} else {
			this.collapsed[col].push(value);
		}
	}

	public isCollapsed(row: number) {
		for (let col = 0; col < this.collapsed.length; col++) {
			const value = this.cell[row][col];
			if (this.collapsed[col] && this.collapsed[col].includes(value)) {
				return true;
			}
		}
		return false;
	}
}

export default Grid;
