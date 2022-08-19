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
    public $where = [
        'and acl_id in (select permission from permissions where role_id=5)',
    ];
    public $sort = [
        '-position',
    ];
}

return Menus::class;
