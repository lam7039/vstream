<?php

namespace controllers;

use SensitiveParameter;

use models\user;
use source\{PageController, Template, Request};

use function source\{
	session_get,
	session_isset,
    session_once,
    session_set,
	session_remove,
};

class authentication extends PageController {
	private user $user;

	public function __construct(Template $templating, Request $request) {
		//TODO: use database class directly instead of user model
        $this->user = new user;
		$parameters = [];
		if ($request->auth_check()) {
            $this->user = $this->user->find(['id' => session_get(env('SESSION_AUTH'))]);
            $parameters['username'] = $this->user->username;
        }
		$parameters['error'] = session_get('error') ?? '';
		$parameters['token'] = $request->csrf_create();

		parent::__construct($templating, $request, $parameters);
	}

	#[\Override]
	public function index(array $parameters = []) : string {
		if ($this->request->auth_check()) {
			redirect('/account');
		}

		return parent::index($parameters);
	}

	public function register(string $username, #[SensitiveParameter] string $password, #[SensitiveParameter] string $confirm) : never {
		//TODO: refine page error checking
		$error = match (true) {
			!$username || !$password || !$confirm => 'A required field is empty',
			$password !== $confirm => 'Password mismatch',
			default => '',
		};
		if ($error) {
			session_once('errors', [
				'register' => $error
			]);
			redirect('/register');
		}

		$password_hash = password_hash($password, PASSWORD_DEFAULT);
		$ip_address = ip2long($_SERVER['REMOTE_ADDR']);
		$this->user->insert(['username' => $username, 'password' => $password_hash, 'ip_address' => $ip_address]);
		$this->login($username, $password);
	}

	public function login(string $username, #[SensitiveParameter] string $password) : never {
		if (session_isset(env('SESSION_AUTH'))) {
			redirect('/account');
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
			// return ['path' => '/account'];
			redirect('/account');
		}

		session_once('errors', [
			'login' => 'Wrong username/password'
		]);
		redirect('/login');
	}

	public function logout() : void {
		session_remove(env('SESSION_AUTH'));
		redirect('/login');
	}
}
