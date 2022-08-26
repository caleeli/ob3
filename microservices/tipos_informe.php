<?php

class TiposInforme
{
    public $table = 'combo_tipo_informe';
    public $id = 'valor';
    public $attributes = [
        'name' => 'descripcion',
    ];
    public $filters = [
        'filterByName(text)' => 'and descripcion like ${"%$text%"}',
    ];
}

return TiposInforme::class;
