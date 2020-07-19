<?php

namespace controllers;

use library\database;
use models\user_access;
use models\user;

use function library\session_exists;
use function library\session_get;
use function library\session_once;
use function library\session_remove;
use function library\session_set;

class authentication implements controller {
	private user $user;
	private user_access $user_access;

	public function __construct(database $database) {
		$this->user = new user($database);
		$this->user_access = new user_access($database);
	}

	public function register(string $username, string $password) : void {
		$password = password_hash($password, PASSWORD_DEFAULT);
		$this->user->insert(['username' => $username, 'password' => $password]);
		redirect('/');
	}

	public function login(string $username, string $password) : void {
		if (session_exists(env('SESSION_AUTH'))) {
			redirect('/');
			return;
		}

		$user = $this->user->find(['username' => $username]);
		if ($user && password_verify($password, $user->password)) {
			if (password_needs_rehash($password, $user->password)) {
				$password = password_hash($password, PASSWORD_DEFAULT);
				$this->user->update(['password' => $password], ['id' => $user->id]);
			}
			$ip_address = ip2long($_SERVER['REMOTE_ADDR']);
			$user_access_id = $this->user_access->insert(['user_id' => $user->id, 'ip_address' => $ip_address]);
			session_set(env('SESSION_AUTH'), $user_access_id);
			redirect('/');
		}

		session_once('incorrect_login', 'Wrong username/password');
		redirect('/');
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