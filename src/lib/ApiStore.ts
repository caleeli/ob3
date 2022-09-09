import type StoreInterface from "./StoreInterface";
import { get, set } from "lodash";
import { login } from '../store';
const backend_base = import.meta.env.VITE_BACKEND_URL;

let headers = {
    'Content-Type': 'application/json',
    'Authorization': '',
};
login.subscribe((data: { attributes: any; relationships: { token: string } } | null) => {
    if (data) {
        headers = {
            'Content-Type': 'application/json',
            'Authorization': 'Bearer ' + data.relationships.token,
        };
    }
});

class ApiStore implements StoreInterface {
    private array: any[] = [];
    private meta: any;
    public offset = 0;
    public limit = 10;
    public searchValue = '';
    public sortBy: { field: string, direction: 'asc' | 'desc' }[] = []
    private listeners: ((data: any[]) => void)[] = [];

    constructor(public config: { url: string, root?: string, query?: any, limit?: number }) { }
    async refresh(): Promise<any> {
        const data = await this.get(0);
        this.listeners.forEach((callback: (arg0: any[]) => any) => callback(data));
        return data;
    }
    onrefresh(callback: (data: any[]) => void): void {
        this.listeners.push(callback);
    }
    async create(record: any): Promise<any> {
        const url = new URL(this.config.url, backend_base);
        let data = {};
        if (this.config.root) {
            set(data, this.config.root, record);
        } else {
            data = record;
        }
        const response = await fetch(url.toString(), { method: 'POST', body: JSON.stringify(data), headers: headers });
        if (!(response.status >= 200 && response.status < 300)) {
            const json = await response.json();
            throw new Error(json.error || response.statusText);
        }
        let result = await response.json();
        if (this.config.root) {
            result = get(result, this.config.root);
        }
        return result;
    }
    async update(id: number | string, record: any): Promise<any> {
        const url = new URL(this.config.url, backend_base);
        let data = {};
        if (this.config.root) {
            set(data, this.config.root, record);
        } else {
            data = record;
        }
        const response = await fetch(url.toString() + '/' + id, { method: 'PUT', body: JSON.stringify(data), headers: headers });
        if (!(response.status >= 200 && response.status < 300)) {
            const json = await response.json();
            throw new Error(json.error || response.statusText);
        }
        let result = await response.json();
        if (this.config.root) {
            result = get(result, this.config.root);
        }
        return result;
    }
    async get(offset?: number): Promise<any[]> {
        const url = new URL(this.config.url, backend_base);
        const params = this.config.query;
        this.limit = this.config.limit === undefined ? this.limit : this.config.limit;
        if (offset !== undefined) {
            this.offset = offset;
        }
        if (params) {
            Object.keys(params).forEach(key => {
                let param = params[key];
                if (params[key] instanceof Function) {
                    // if value is function, add function result as param
                    param = params[key](this);
                }
                if (Array.isArray(param)) {
                    // if value is array, add multiple params
                    param.forEach((value: string) => url.searchParams.append(key + '[]', value));
                } else if (param instanceof Object) {
                    // if value is object, add multiple params
                    Object.keys(param).forEach(value => url.searchParams.append(key + '[' + value + ']', param[value]));
                } else if (param !== undefined) {
                    url.searchParams.append(key, param);
                }
            });
        }
        const response = await fetch(url.toString(), { headers: headers });
        if (!(response.status >= 200 && response.status < 300)) {
            const json = await response.json();
            throw new Error(json.error || response.statusText);
        }
        const response_json = await response.json();
        if (this.config.root) {
            this.meta = get(response_json, 'meta');
            this.array = get(response_json, this.config.root);
        }
        return this.array;
    }
    async show(id: string | number | null, params?: any): Promise<any[]> {
        const url = new URL(this.config.url + '/' + id, backend_base);
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
        const response = await fetch(url.toString(), { headers: headers });
        if (!(response.status >= 200 && response.status < 300)) {
            const json = await response.json().catch(() => ({ error: response.statusText }));
            throw json.error;
        }
        let result = await response.json();
        if (this.config.root) {
            result = get(result, this.config.root);
        }
        return result;
    }
    async delete(id: string | number): Promise<any> {
        const url = new URL(this.config.url, backend_base);
        const response = await fetch(url.toString() + '/' + id, { method: 'DELETE', headers: headers });
        return response;
    }
    async getNextPage(): Promise<any[]> {
        this.offset += this.limit;
        return await this.get(this.offset);
    }
    async length(): Promise<number> {
        return this.array.length;
    }
    async sort(sort: { field: string, direction: 'asc' | 'desc' }[]): Promise<any[]> {
        this.sortBy = sort;
        return this.get(0);
    }
    setSort(sort: { field: string, direction: 'asc' | 'desc' }[]): void {
        this.sortBy = sort;
    }
    getMeta() {
        return this.meta;
    }
}

export default ApiStore;

export function JSONApiSortHandler(grid: { sortBy: { field: string; direction: 'asc' | 'desc' }[]; }): string {
    return grid.sortBy.map(({ direction, field }) => `${direction === 'desc' ? '-' : ''} ${field}`).join(',');
}

export function JSONApiPageHandler(grid: { offset: number; limit: number }): number {
    return grid.offset / grid.limit + 1;
}

export function JSONApiPerPageHandler(grid: { limit: number }): number {
    return grid.limit;
}
