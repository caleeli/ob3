import type StoreInterface from './StoreInterface';
import { get } from 'lodash';
import BaseStore from './BaseStore';

class ArrayStore extends BaseStore implements StoreInterface {
	constructor(private array: any[]) {
		super();
	}
	getMeta() {
		return {};
	}
	show(id: string | number, params?: any): Promise<any[]> {
		throw new Error('Method "show" not implemented.', params);
	}
	delete(id: string | number): Promise<any> {
		throw new Error('Method "delete" not implemented.', id);
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
