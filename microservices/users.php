<?php

class Users
{
    public $table = 'users';
    public $id = 'adusrcorr';
    public $attributes = [
        'name' => 'adusrnomb',
    ];
    public $update = [
        'adusrnomb'=> ':name',
        'updated_at'=> 'now()',
        'adusrpass'=> '${md5($password)}',
    ];
    public $filters = [
        'filterByName(text)' => 'and adusrnomb like ${"%$text%"}',
    ];
}

return Users::class;
