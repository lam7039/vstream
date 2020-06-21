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

            $env_config = explode('=', $line);

            //Get complete value after the first '='
            $env_config_value = '';
            foreach ($env_config as $key => $value) {
                if ($key) {
                    $env_config_value .= $value;
                }
            }

            $this->config[$env_config[0]] = trim($env_config_value);
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