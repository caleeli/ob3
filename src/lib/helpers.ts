import ApiStore from './ApiStore';

export function comboStore(
	url: string,
	storeValueField = 'id',
	storeNameField = 'attributes.name'
) {
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

export function feel(expression: string, $page: any) {
	const context: { [key: string]: any } = {
		now: new Date(),
		$url: new Proxy(
			{},
			{
				get: (target, prop) => {
					return $page.url.searchParams.get(prop);
				},
			}
		),
	};
	const keys = Object.keys(context);
	const values = keys.map((key) => context[key]);
	return new Function(...keys, 'return ' + expression)(...values);
}
