<?php

namespace controllers;

use library\database;
use models\access;
use models\user;

use function library\session_get;
use function library\session_once;
use function library\session_remove;
use function library\session_set;

//TODO: create an access table and store the access id into the session instead of the user id
class authentication implements controller {
	private user $user;
	private access $access;

	public function __construct(database $database) {
		$this->user = new user($database);
		$this->access = new access($database);
	}

	public function register(string $username, string $password) : void {
		$salt = bin2hex(openssl_random_pseudo_bytes(11));
		$password = hash('sha256', $salt . $password);
		$this->user->insert(['username', 'password', 'salt'], [$username, $password, $salt]);
		redirect('/');
	}

	public function login(string $username, string $password) : void {
		$user = $this->user->find(['username' => $username]);
		if ($user && $user->password === hash('sha256', $user->salt . $password)) {
			$ip_address = ip2long($_SERVER['REMOTE_ADDR']);
			$access_id = $this->access->insert(['user_id', 'ip_address'], [$user->id, $ip_address]);
			session_set(env('SESSION_AUTH'), $access_id);
			redirect('/');
		}

		session_once('incorrect_login', 'Wrong username/password');
		redirect('/');
	}

	public function logout() : void {
		$this->access->delete(session_get(env('SESSION_AUTH')));
		session_remove(env('SESSION_AUTH'));
		redirect('/');
	}

	//public function find_access() : void {
	//	$access = $this->access->find(['ip_address' => ip2long($_SERVER['REMOTE_ADDR'])], ['id']);
	//	if ($access) {
	//		session_set('access', true);
	//		session_set(env('SESSION_AUTH'), $access->id);
	//	} else {
	//		session_set('access', false);
	//	}
	//	redirect('/');
	//}

}