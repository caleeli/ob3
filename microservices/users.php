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
    ];
}

return Users::class;
