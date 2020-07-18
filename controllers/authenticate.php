<?php

namespace controllers;

use library\database;
use models\user;

use function library\session_get;
use function library\session_once;
use function library\session_remove;
use function library\session_set;

//TODO: create an access table and store the access id into the session instead of the user id
class authentication implements controller {
	private $database;

	public function __construct(database $database) {
		$this->database = $database;
	}

	public function register(string $username, string $password) : void {
		$salt = bin2hex(openssl_random_pseudo_bytes(11));
		if ($this->database->execute(
			'insert into users (username, password, salt) values (:username, :password, :salt)', 
			['username' => $username, 'password' => hash('sha256', $salt . $password), 'salt' => $salt]
		)) {
			redirect('/');
		}
	}

	public function login(string $username, string $password) : void {
		$user = new user($this->database);
		$user = $user->find(['username' => $username]);
		if ($user && $user->password === hash('sha256', $user->salt . $password)) {
			$this->database->execute('insert into access (user_id) values (:user_id)', ['user_id' => $user->id]);
			session_set(env('SESSION_AUTH'), $this->database->last_inserted_id);
			redirect('/');
		}

		session_once('incorrect_login', 'Wrong username/password');
		redirect('/');
	}

	public function logout() : void {
		$this->database->execute('delete from access where id = :id', ['id' => session_get(env('SESSION_AUTH'))]);
		session_remove(env('SESSION_AUTH'));
		redirect('/');
	}

}