<?php

class Calidades
{
    public $table = 'combo_calidad';
    public $id = 'valor';
    public $attributes = [
        'name' => 'descripcion',
    ];
    public $filters = [
        'filterByName(text)' => 'and descripcion like ${"%$text%"}',
    ];
}

return Calidades::class;
