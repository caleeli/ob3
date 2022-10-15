<?php

class Agencias
{
	public $table = 'agencias';
	public $id = 'valor';
	public $attributes = [
		'name' => 'descripcion',
	];
	public $filters = [
		'filterByName($text)' => 'and descripcion like ${"%$text%"}',
	];
}

return Agencias::class;
