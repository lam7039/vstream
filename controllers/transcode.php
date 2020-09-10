<?php

namespace controllers;

use source\builder;
use source\database;
use source\transcoder;
use source\video_buffer;

class transcode implements controller {
    private database $database;
    private transcoder $transcoder;

    public function __construct(database $database) {
        $this->database = $database;
        $this->transcoder = new transcoder;
    }

    public function run() : void {
        // $media_builder = new builder($this->database, 'media');
        // $jobs_builder = new builder($this->database, 'scheduled_jobs');
        // // $jobs_builder->insert();
        // $jobs = $jobs_builder->find([], ['*']);

        // // returns 0 if there are one or more processes running
        // if (!($command_is_running = shell_exec('pgrep ffmpeg'))) {
        //     return;
        // }
        
        // while ($jobs->count() /* && !$script_already_running */) {
        //     // $this->transcoder->option_set('codec', '-c:v libx265');
        //     $job = $jobs_builder->find([], ['*'], 1);
        //     $item = $media_builder->find(['id' => $job->id]);

            // $buffer = new video_buffer($item->filename, 10);
            $buffer = new video_buffer('D:/xampp/htdocs/Baka to Test to Shoukanjuu Matsuri - NCOP.mkv', 10);
            $this->transcoder->ffmpeg($buffer);

            // $jobs_builder->delete(['id' => $job->id]);
            // $jobs = $jobs_builder->find([], ['*']);
        // }
    }
}
