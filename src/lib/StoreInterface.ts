
interface StoreInterface {

    get(offset?: number): Promise<any[]>;
    getNextPage(): Promise<any[]>;
    length(): Promise<number>;
    sort(sort: { field: string, direction: 'asc' | 'desc' }[]): Promise<any[]>;
    setSort(sort: { field: string, direction: 'asc' | 'desc' }[]): void;
    create(record: any): Promise<any>;
    update(id: number | string, record: any): Promise<any>;
    delete(id: number | string): Promise<any>;
    refresh(): Promise<any>;
    onrefresh(callback: (data: any[]) => void): void;
}

export default StoreInterface;
