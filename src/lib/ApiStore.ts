import type StoreInterface from "./StoreInterface";
import { get } from "lodash";

class ApiStore implements StoreInterface {
    private array: any[] = [];
    public offset = 0;
    public limit = 10;
    public sort_field = '';
    public sort_direction: 'asc' | 'desc' = 'asc';

    constructor(private config: { url: string, root: string, query: any, limit: number }) { }
    async get(offset?: number): Promise<any[]> {
        const url = new URL(this.config.url);
        const params = this.config.query;
        this.limit = this.config.limit;
        if (offset!==undefined) {
            this.offset = offset;
        }
        if (params) {
            Object.keys(params).forEach(key => {
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
        }
        const response = await fetch(url.toString());
        this.array = await response.json();
        if (this.config.root) {
            this.array = get(this.array, this.config.root);
        }
        return this.array;
    }
    async getNextPage(): Promise<any[]> {
        this.offset += this.limit;
        return await this.get(this.offset);
    }
    async length(): Promise<number> {
        return this.array.length;
    }
    async sort(field: string, direction: 'asc' | 'desc'): Promise<any[]> {
        this.sort_field = field;
        this.sort_direction = direction;
        return this.get(0);
    }
}

export default ApiStore;