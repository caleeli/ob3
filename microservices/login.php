<?php

class Login
{
    public $table = 'login';
    public $id = 'id';
    public $attributes = [
        'user_id' => 'user_id',
        'username' => 'username',
    ];
    public $create = [
        'username'=> ':username',
        'user_id'=> '${Login::getUserId($username, $password)}',
        'created_at'=> 'now()',
    ];
    public $returnCreatedRecord = true;

    public static function getUserId($username, $password)
    {
        global $connection;
        $stmt = $connection->prepare('select adusrcorr as id, adusrpass as password from users where adusrlogi=:username');
        $stmt->execute([':username'=>$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        //if ($user && password_verify($password, $user['password'])) {
        if ($user && md5($password) == $user['password']) {
            return $user['id'];
        } else {
            throw new Exception('Invalid user credentials', 401);
        }
    }
}

return Login::class;
