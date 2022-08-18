import type StoreInterface from "./StoreInterface";
import { get } from "lodash";

class ArrayStore implements StoreInterface {

    constructor(private array: any[]) {
    }
    delete(id: string | number): Promise<any> {
        throw new Error("Method not implemented.");
    }
    onrefresh(callback: (data: any[]) => void): void {
        throw new Error("Method not implemented.");
    }
    refresh(): Promise<any> {
        throw new Error("Method not implemented.");
    }
    create(record: any): Promise<any> {
        throw new Error("Method not implemented.");
    }
    update(id: number|string, record: any): Promise<any> {
        throw new Error("Method not implemented.");
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
    async sort(field: string, order: 'asc' | 'desc'): Promise<any[]> {
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
