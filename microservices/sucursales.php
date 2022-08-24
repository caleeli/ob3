<?php

class Sucursales
{
    public $table = 'combo_sucursal';
    public $id = 'valor';
    public $attributes = [
        'name' => 'descripcion',
    ];
    public $filters = [
        'filterByName(text)' => 'and descripcion like ${"%$text%"}',
    ];
}

return Sucursales::class;
