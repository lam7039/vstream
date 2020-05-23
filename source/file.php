<?php

namespace library;

use library\file_buffer;

class file {

    public static function write(string $path, string $data) : bool {
        if (!self::exists($path)) {
            return false;
        }
        file_put_contents($path, $data);
        return true;
    }

	public static function load(string $path) : string {
		if (!self::exists($path)) {
			return "";
		}
		return file_get_contents($path);
    }

    public static function create_buffer(string $path) : file_buffer {
        $body = self::load($path);
        return new file_buffer($path, $body, strlen($body));
    }
    
    private static function exists(string $path) : bool {
        if (!file_exists($path)) {
			LOG_WARNING("File does not exist: $path");
            return false;
        }
        return true;
    }
}