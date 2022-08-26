import ApiStore from "./ApiStore";

export function comboStore(url: string, storeValueField = 'id', storeNameField = 'attributes.name') {
    return {
        storeValueField,
        storeNameField,
        store: new ApiStore({
            url,
            root: 'data',
            query: {
                filter: (store: ApiStore) => [`filterByName(${JSON.stringify(store.searchValue)})`],
            },
        }),
    };
}
