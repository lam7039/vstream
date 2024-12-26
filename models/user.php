<?php

namespace models;

class user extends model {
    protected string $table = 'users';
    private(set) int $id;
    private(set) string $username;
    private(set) string $password;
    private(set) int $ip_address;
}
