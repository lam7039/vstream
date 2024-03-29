<?php

namespace controllers;

use models\user;

use function source\{session_get, session_isset, session_set, session_remove, auth_check, csrf_create};

class authentication extends page_controller {
	private user $user;

	public function __construct(array $parameters = []) {
        $this->user = new user;
		if (auth_check()) {
            $this->user = $this->user->find(['id' => session_get(env('SESSION_AUTH'))]);
            $this->parameters['username'] = $this->user->username;
        }
		$this->parameters['error'] = session_get('error') ?? '';
		$this->parameters['token'] = csrf_create();
		parent::__construct($parameters);
	}

	public function register(string $username, string $password, string $confirm) : array {
		//TODO: refine page error checking
		$error = match (true) {
			!$username || !$password || !$confirm => 'A required field is empty',
			$password !== $confirm => 'Password mismatch',
			default => '',
		};
		if ($error) {
			return [
				'path' => '/register',
				'error' => $error
			];
		}

		$password_hash = password_hash($password, PASSWORD_DEFAULT);
		$ip_address = ip2long($_SERVER['REMOTE_ADDR']);
		$this->user->insert(['username' => $username, 'password' => $password_hash, 'ip_address' => $ip_address]);
		return $this->login($username, $password);
	}

	public function login(string $username, string $password) : array {
		if (session_isset(env('SESSION_AUTH'))) {
			return ['path' => '/account'];
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
			return ['path' => '/account'];
		}

		return [
			'path' => '/login',
			'error' => 'Wrong username/password'
		];
	}

	public function logout() : void {
		session_remove(env('SESSION_AUTH'));
	}
}
