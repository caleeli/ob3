<?php

class TiposObservacion
{
    public $table = 'ci_tipo_obser';
    public $id = 'valor';
    public $attributes = [
        'name' => 'descripcion',
    ];
    public $filters = [
        'filterByName(text)' => 'and descripcion like ${"%$text%"}',
    ];
}

return TiposObservacion::class;
