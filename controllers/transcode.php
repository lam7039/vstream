<?php

namespace controllers;

use source\database;
use source\transcoder;
use source\video_buffer;

class transcode implements controller {
    public transcoder $transcoder;

    public function __construct(database $database) {
        $this->transcoder = new transcoder($database);
    }

    public function run() {
        $buffer = new video_buffer('video.mkv', 10);
        $this->transcoder->ffmpeg($buffer);
    }
}