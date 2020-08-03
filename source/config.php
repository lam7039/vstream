<?php

namespace source;

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

            [$key, $value] = explode('=', $line, 2);
            $this->config[$key] = trim($value);
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
