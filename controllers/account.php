<?php

namespace controllers;

class account extends controller {
    private $user = null;

    public function __construct(string $url_page) {
        $this->parameters['ip'] = $this->user ? long2ip($this->user->ip_address) : '';
        $this->parameters['testfor'] = [
            'first',
            'second',
            'third',
        ];
        parent::__construct($url_page);
    }
}
