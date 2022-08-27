import type StoreInterface from "./StoreInterface";
import ApiStore from "./ApiStore";

class ConfigStore {
    private store: StoreInterface;

    constructor(private id: string, private config: any) {
        this.store = new ApiStore({
            url: 'edit_page_config',
        });
    }
    async save(): Promise<any> {
        return this.store.update(this.id, { data: this.config });
    }
}

export default ConfigStore;
