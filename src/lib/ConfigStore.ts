import type StoreInterface from './StoreInterface';
import ApiStore from './ApiStore';

class ConfigStore {
	private store: StoreInterface;

	constructor(private id: string, private config: any) {
		this.store = new ApiStore({
			url: 'edit_page_config',
			root: 'data',
		});
	}

	async save(): Promise<any> {
		return this.store.update(this.id, this.config);
	}

	async create(): Promise<any> {
		return this.store.create(this.config);
	}

	getModelMeta(model: string): Promise<any> {
		const store = new ApiStore({
			url: 'edit_page_model_meta',
			root: 'data',
		});
		return store.show(model);
	}
}

export default ConfigStore;
