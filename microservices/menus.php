<?php

class Menus
{
    public $table = 'menus';
    public $id = 'id';
    public $attributes = [
        'text' => 'text',
        'parent' => 'parent',
        'type' => 'type',
        'leaf' => 'leaf',
    ];
    public $filters = [
    ];
}

return Menus::class;
