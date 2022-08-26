<?php

class TipoCredito
{
    public $table = 'tipo_credito';
    public $id = 'id';
    public $attributes = [
        'name' => 'nombre',
    ];
    public $filters = [
        'filterByName(text)' => 'and nombre like ${"%$text%"}',
    ];
}

return TipoCredito::class;
