import type StoreInterface from './StoreInterface';
import { get } from 'lodash';

class ArrayStore implements StoreInterface {
	constructor(private array: any[]) {}
	getMeta() {
		return {};
	}
	show(id: string | number, params?: any): Promise<any[]> {
		throw new Error('Method not implemented.', params);
	}
	setSort(sort: { field: string; direction: 'asc' | 'desc' }[]): void {
		console.warn('Method not implemented.', sort);
	}
	delete(id: string | number): Promise<any> {
		throw new Error('Method not implemented.', id);
	}
	onrefresh(callback: (data: any[]) => void): void {
		console.warn('Method not implemented.', callback);
	}
	refresh(): Promise<any> {
		throw new Error('Method not implemented.');
	}
	create(record: any): Promise<any> {
		throw new Error('Method not implemented.', record);
	}
	update(id: number | string, record: any): Promise<any> {
		throw new Error('Method not implemented.', id, record);
	}
	async get(): Promise<any[]> {
		return this.array;
	}
	async getNextPage(): Promise<any[]> {
		return [];
	}
	async length(): Promise<number> {
		return this.array.length;
	}
	async sort(sort: { field: string; direction: 'asc' | 'desc' }[]): Promise<any[]> {
		const field = sort[0].field;
		const order = sort[0].direction;
		this.array.sort((a, b) => {
			if (get(a, field) < get(b, field)) {
				return order === 'asc' ? -1 : 1;
			}
			if (get(a, field) > get(b, field)) {
				return order === 'asc' ? 1 : -1;
			}
			return 0;
		});
		return this.array;
	}
}

export default ArrayStore;
