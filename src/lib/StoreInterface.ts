
interface StoreInterface {

    get(): Promise<any[]>;
    getNextPage(): Promise<any[]>;
    length(): Promise<number>;
    sort(field: string, order: 'asc' | 'desc'): Promise<any[]>;
}

export default StoreInterface;
