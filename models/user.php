<?php

namespace models;

class user extends model {
    protected string $table = 'users';
    public int $id;
    public string $username;
    public string $password;
    public int $ip_address;

    // private(set) int $id;
    // private(set) string $username;

    // public function __construct($id, $username)
    // {
    //     parent::__construct()
    // }
}
