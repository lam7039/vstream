<?php

namespace controllers;

use models\user;
use source\request;

use function source\session_isset;
use function source\session_set;
use function source\session_remove;
use function source\session_once;

class authentication extends controller {
	private user $user;

	public function __construct(request $request = null) {
		parent::__construct($request);
		$this->user = new user;
	}

	public function register() : void {
		if ($this->request->password !== $this->request->confirm) {
			session_once('password_mismatch', 'Password mismatch');
			redirect('/register');
		}

		$password_hash = password_hash($this->request->password, PASSWORD_DEFAULT);
		$ip_address = ip2long($_SERVER['REMOTE_ADDR']);
		$this->user->insert(['username' => $this->request->username, 'password' => $password_hash, 'ip_address' => $ip_address]);
		$this->login($this->request->username, $this->request->password);
	}

	public function login() : void {
		if (session_isset(env('SESSION_AUTH'))) {
			return;
		}

		$user = $this->user->find(['username' => $this->request->username]);
		if ($user && password_verify($this->request->password, $user->password)) {
			if (password_needs_rehash($this->request->password, $user->password)) {
				$password = password_hash($this->request->password, PASSWORD_DEFAULT);
				$this->user->update(['password' => $password], ['id' => $user->id]);
			}
			$ip_address = ip2long($_SERVER['REMOTE_ADDR']);
			$this->user->update(['ip_address' => $ip_address], ['id' => $user->id]);
			session_set(env('SESSION_AUTH'), $user->id);
			return;
		}

		session_once('incorrect_login', 'Wrong username/password');
		redirect('/login');
	}

	public function logout() : void {
		session_remove(env('SESSION_AUTH'));
	}
}