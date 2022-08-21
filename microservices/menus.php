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
        'page' => 'page',
    ];
    public $filters = [
    ];
    public $where = [
        'and acl_id in (select permission from permissions where role_id=5)',
    ];
}

return Menus::class;
