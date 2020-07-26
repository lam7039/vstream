<?php

namespace controllers;

use source\database;
use models\user_access;
use models\user;

use function source\session_isset;
use function source\session_get;
use function source\session_once;
use function source\session_remove;
use function source\session_set;

class authentication implements controller {
	private user $user;
	private user_access $user_access;
	private string $username;
	private string $password;
	private string $password_verification;

	public function __construct(database $database) {
		$this->user = new user($database);
		$this->user_access = new user_access($database);

		//TODO: retrieve post variables somewhere else
		$this->username = $_POST['username'] ?? '';
		$this->password = $_POST['password'] ?? '';
		$this->password_verification = $_POST['password_verification'] ?? '';
	}

	public function register() : void {
		if ($this->password !== $this->password_verification) {
			session_once('password_mismatch', 'Password mismatch');
			redirect('/register');
			return;
		}
		$this->password = password_hash($this->password, PASSWORD_DEFAULT);
		$this->user->insert(['username' => $this->username, 'password' => $this->password]);
		redirect('/');
	}

	public function login() : void {
		if (session_isset(env('SESSION_AUTH'))) {
			redirect('/');
			return;
		}

		$user = $this->user->find(['username' => $this->username]);
		if ($user && password_verify($this->password, $user->password)) {
			if (password_needs_rehash($this->password, $user->password)) {
				$this->password = password_hash($this->password, PASSWORD_DEFAULT);
				$this->user->update(['password' => $this->password], ['id' => $user->id]);
			}
			$ip_address = ip2long($_SERVER['REMOTE_ADDR']);
			$user_access_id = $this->user_access->insert(['user_id' => $user->id, 'ip_address' => $ip_address]);
			session_set(env('SESSION_AUTH'), $user_access_id);
			redirect('/');
			return;
		}

		session_once('incorrect_login', 'Wrong username/password');
		redirect('/login');
	}

	public function logout() : void {
		$this->user_access->delete(['id' => session_get(env('SESSION_AUTH')) ?? 0]);
		session_remove(env('SESSION_AUTH'));
		redirect('/');
	}

	//public function find_user_access() : void {
	//	$user_access = $this->user_access->find(['ip_address' => ip2long($_SERVER['REMOTE_ADDR'])], ['id']);
	//	if ($user_access) {
	//		session_set('user_access', true);
	//		session_set(env('SESSION_AUTH'), $user_access->id);
	//	} else {
	//		session_set('user_access', false);
	//	}
	//	redirect('/');
	//}

}