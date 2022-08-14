
interface StoreInterface {

    get(offset?: number): Promise<any[]>;
    getNextPage(): Promise<any[]>;
    length(): Promise<number>;
    sort(field: string, direction: 'asc' | 'desc'): Promise<any[]>;
}

export default StoreInterface;
