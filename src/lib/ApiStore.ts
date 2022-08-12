import type StoreInterface from "./StoreInterface";
import { get } from "lodash";

class ApiStore implements StoreInterface {
    private array: any[] = [];
    public offset = 0;

    constructor(private config: { url: string, root: string, query: any }) { }
    async get(): Promise<any[]> {
        const url = new URL(this.config.url);
        const params = this.config.query;
        Object.keys(params).forEach(key => {
            console.log(key, params[key], params[key] instanceof Function);
            if (Array.isArray(params[key])) {
                // if value is array, add multiple params
                params[key].forEach((value: string) => url.searchParams.append(key + '[]', value));
            } else if (params[key] instanceof Function) {
                // if value is function, add function result as param
                url.searchParams.append(key, params[key](this));
            } else if (params[key] instanceof Object) {
                // if value is object, add multiple params
                Object.keys(params[key]).forEach(value => url.searchParams.append(key + '[' + value + ']', params[key][value]));
            } else if (params[key] !== undefined) {
                url.searchParams.append(key, params[key]);
            }
        });
        const response = await fetch(url.toString());
        this.array = await response.json();
        if (this.config.root) {
            this.array = get(this.array, this.config.root);
        }
        return this.array;
    }
    async getNextPage(): Promise<any[]> {
        this.offset += this.array.length;
        return await this.get();
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

export default ApiStore;
