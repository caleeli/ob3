import type GridConfig from "./GridConfig";
import type StoreInterface from "./StoreInterface";

class Grid {
	private config: GridConfig;
	public cell: any[][] = [];
	private data: any[] = [];
	public store: StoreInterface;
	public error = "";
	// Constructor
	constructor(config: GridConfig, store: StoreInterface) {
		this.config = config;
		this.store = store;
		store.setSort(config.sort.field, config.sort.order);
	}

	public async load() {
		const data = await this.store.get(0);
		this.cell.splice(0);
		this.hydrate(data);
	}

	public async loadFromData(data: any[]) {
		this.data.splice(0);
		this.cell.splice(0);
		this.hydrate(data);
	}

	public async loadNextPage() {
		const data = await this.store.getNextPage();
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

	public async sort(field: string, direction: 'asc' | 'desc'): Promise<void> {
		const data = await this.store.sort(field, direction);
		this.cell.splice(0);
		this.hydrate(data);
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
		let colspan = 0;
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
}

export default Grid;
