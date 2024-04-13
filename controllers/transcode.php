<?php

namespace controllers;

use source\{controller, mysql_builder, media_buffer, transcoder};

class transcode extends controller {
    private transcoder $transcoder;

    public function __construct() {
        $this->transcoder = new transcoder;
    }

    public function run(media_buffer $buffer) : void {
        if (!in_array($buffer->type, ['video', 'audio'])) {
            LOG_WARNING('Type incompatible, cannot be transcoded');
            return;
        }

        // TODO: ping database to check connection, if no connection, create new one (closes after 8 hours by default)
        // TODO: use singleton for transcoder?
        $media_builder = new mysql_builder('media');
        $jobs_builder = new mysql_builder('scheduled_jobs');
        $jobs_builder->insert([]);
        $jobs = $jobs_builder->find();

        // returns 0 if there are one or more processes running
        if (!($command_is_running = shell_exec('pgrep ffmpeg'))) {
            return;
        }
        
        while ($jobs->count() && !$command_is_running) {
            $job = $jobs_builder->find(limit: 1);
            $item = $media_builder->find(['id' => $job->id]);
            // $buffer = new $buffer_name($item->filename, 10);
            // $buffer = new video_buffer('D:/xampp/htdocs/Baka to Test to Shoukanjuu Matsuri - NCOP.mkv', 10);
            // $buffer->subtitles_type = 'soft';
            // $buffer = new audio_buffer('D:/xampp/htdocs/ikenai borderline.mp3');
            $this->transcoder->ffmpeg($buffer);

            $jobs_builder->delete(['id' => $job->id]);
            $jobs = $jobs_builder->find();
        }
    }
}
