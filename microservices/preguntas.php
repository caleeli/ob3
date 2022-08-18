<?php

class Preguntas
{
    public $table = 'preguntas';
    public $id = 'id';
    public $attributes = [
        'number' => 'number',
        'id_grupo' => 'id_grupo',
        'indice' => 'indice',
        'descripcion' => 'descripcion',
        'tipo_credito' => 'tipo_credito',
    ];
    public $filters = [
        'tipo_credito(tipo_credito)' => 'and tipo_credito=:tipo_credito',
    ];
    public $create = [
        'number' => ':number',
        'id_grupo' => ':id_grupo',
        'indice' => ':indice',
        'descripcion' => ':descripcion',
        'tipo_credito' => ':tipo_credito',
    ];
    public $update = [
        'number' => ':number',
        'id_grupo' => ':id_grupo',
        'indice' => ':indice',
        'descripcion' => ':descripcion',
        'tipo_credito' => ':tipo_credito',
    ];
}

return Preguntas::class;
