<?php

namespace controllers;

use source\database;
use models\user;

use function source\session_isset;
use function source\session_once;
use function source\session_remove;
use function source\session_set;

class authentication implements controller {
	private user $user;

	public function __construct(database $database) {
		$this->user = new user($database);
	}

	public function register(string $username, string $password, string $verification) : void {
		if ($password !== $verification) {
			session_once('password_mismatch', 'Password mismatch');
			redirect('/register');
			return;
		}
		$password_hash = password_hash($password, PASSWORD_DEFAULT);
		$ip_address = ip2long($_SERVER['REMOTE_ADDR']);
		$this->user->insert(['username' => $username, 'password' => $password_hash, 'ip_address' => $ip_address]);
		$this->login($username, $password);
	}

	public function login(string $username, string $password) : void {
		if (session_isset(env('SESSION_AUTH'))) {
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
			$this->user->update(['ip_address' => $ip_address], ['id' => $user->id]);
			session_set(env('SESSION_AUTH'), $user->id);

			redirect('/');
			return;
		}

		session_once('incorrect_login', 'Wrong username/password');
		redirect('/login');
	}

	public function logout() : void {
		session_remove(env('SESSION_AUTH'));
		redirect('/');
	}
}