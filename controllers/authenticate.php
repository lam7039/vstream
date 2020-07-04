<?php

namespace controllers;

use library\database;

use function library\session_remove;
use function library\session_set;

class authentication {
	private $database;

	public function __construct(database $database) {
		$this->database = $database;
	}

	public function register(string $username, string $password) : bool {
		$salt = uniqid(mt_rand(), true);
		return $this->database->execute(
			'insert into users (username, password, salt, pepper) values (:username, :password, :salt)', 
			['username' => $username, 'password' => hash('sha256', $salt . $password), 'salt' => $salt]
		);
	}

	private function find_user($username) : ?object {
		$sql = 'select username from users where username = :username';
		$user = $this->database->fetch($sql, ['username' => $username]);
		return $user->count() === 1 ? $user : null;
	}

	public function login(string $username, string $password) : bool {
		$user = $this->find_user($username);
		if ($user && $user->password === hash('sha256', $user->salt . $password)) {
			session_set('auth', $user->id);
			return true;
		}
		return false;
	}

	public function logout() : void {
		session_remove('auth');
	}

}