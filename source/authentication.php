<?php

namespace library;

class authentication {
	private $database;

	public function __construct(database $database) {
		$this->database = $database;
	}

	public function register(string $username, string $password, string $salt) : bool {
		//TODO: create proper registration, also with salt & pepper
		$sql = [
			true,
			'insert into users (username, password, salt) values (:username3, :password3, :salt3)',
			'insert into users (username, password, salt) values (:username4, :password4, :salt4)'
		];
		$variables = [
			['username3' => 'username3', 'password3' => $password, 'salt3' => $salt],
			['username4' => 'username4', 'password4' => $password, 'salt4' => $salt]
		];
		return $this->database->execute_multiple($sql, $variables);
	}

	private function find_user($username, $password) : bool {
		$sql = 'select username from users where username = :username and password = :password';
		return $this->database->fetch($sql, ['username' => $username, 'password' => $password])->count() === 1;
	}

	public function login(string $username, string $password) : bool {
		//TODO: create proper authentication
		if (!$this->find_user($username, $password)) {
			return false;
		}
		//TODO: create session
		return true;
	}

	public function logout() : bool {
		//TODO: remove session if logged in
		return true;
	}

	public function details() : array {
		//TODO: check if logged in, then find details for current user
		return ['account data'];
	}

}