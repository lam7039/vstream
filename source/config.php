<?php

namespace library;

class config {
    private array $config = [];

    public function __construct() {
        $environment_file = file_get_contents('.env', true);
        $lines = explode("\n", $environment_file);

        foreach ($lines as $line) {
            $line = trim($line);
            if (!$line) {
                continue;
            }

            $variable = explode('=', $line);
            $this->config[$variable[0]] = $variable[1];
        }
    }

    public function get($key) {
        if(!isset($this->config[$key])) {
            LOG_WARNING('Configuration does not exist');
            return false;
        }
        return $this->config[$key];
    }
}