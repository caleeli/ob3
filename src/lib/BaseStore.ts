abstract class BaseStore {
	public offset = 0;
	public sortBy: { field: string; direction: 'asc' | 'desc' }[] = [];
	protected listeners: ((data: any[]) => void)[] = [];

	onrefresh(callback: (data: any[]) => void): void {
		this.listeners.push(callback);
	}

	async refresh(): Promise<any> {
		console.log('refresh');
		const data = await this.get(0);
		this.listeners.forEach((callback: (arg0: any[]) => any) => callback(data));
		return data;
	}

	setSort(sort: { field: string; direction: 'asc' | 'desc' }[]): void {
		this.sortBy = sort;
	}

	async get(offset?: number): Promise<any[]> {
		if (offset !== undefined) {
			this.offset = offset;
		}
		return [];
	}
}

export default BaseStore;
