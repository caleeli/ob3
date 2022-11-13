<?php

global $_PATH;

if ($_SERVER['REQUEST_METHOD']==='POST') {
    $name = $_POST['id'];
    $configFile = __DIR__ . '/../src/routes/' . $name . '/config.json';
    $codeFile = __DIR__ . '/../src/routes/' . $name . '.svelte';
    $config = json_encode($_POST['data'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    $code = <<<CODE
	<script lang="ts">
		import type CrudAction from '../lib/CrudAction';
		import ApiStore, {
			JSONApiPageHandler,
			JSONApiPerPageHandler,
			JSONApiSortHandler,
		} from '../lib/ApiStore';
		import type GridConfig from '../lib/GridConfig';
		import Crud from '../stories/CRUD.svelte';
		import page_config from './{$name}.json';
		import ConfigStore from '../lib/ConfigStore';

		let configStore = new ConfigStore('{$name}', page_config);
		let config: GridConfig = page_config.grid;
		let store = new ApiStore(
			Object.assign(page_config.store, {
				root: 'data',
				query: {
					per_page: JSONApiPerPageHandler,
					page: JSONApiPageHandler,
					sort: JSONApiSortHandler,
				},
			})
		);
		let toolbar: CrudAction[] = [];
	</script>

	<Crud {config} {store} {toolbar} {configStore} />
	CODE;

    file_put_contents($configFile, $config);
    file_put_contents($codeFile, $code);
}

if ($_SERVER['REQUEST_METHOD']==='PUT') {
    $id = $_PATH[1];
    $configFile = __DIR__ . '/../src/routes/' . $_PATH[1] . '/config.json';
    $config = json_encode($_POST['data'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    file_put_contents($configFile, $config);
}

return [];
