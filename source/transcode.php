<?php

namespace library;

class video_transcode_buffer {
    public array $transcoded_times;
    
    public function add_transcode(int $time_start, int $time_duration) : string {
        if (isset($this->transcoded_times[$time_start])) {
            return date('H:i:s', $time_start + $this->transcoded_times[$time_start]);
        }

        $this->transcoded_times[$time_start] = $time_duration = $time_start;
    }
}

class video_buffer {
    public string $path_source;
    public string $path_output;
    public string $format;
    public int $duration;
    public string $duration_time;
    public video_transcode_buffer $transcode_buffer;

    public function __construct(string $path_source, string $format, int $duration) {
        $this->format = $format;
        $this->path_source = $path_source;
        $this->path_output = "Other_Path/";
        $this->duration = $duration;
        $this->duration_time = date('H:i:s', $duration);
    }
}

class transcode {
    //TODO: make this work
    //TODO: figure out interrupting and continuing transcoding at selected time without redoing the transcode, also multistream encoding
    //TODO: maybe do fully encoding and display which have and which haven't been encoded in the interface
    //TODO: follow file system for path

    public video_buffer $video;
    
    public function __construct(string $path, string $format, int $duration) {
        $this->video = new video_buffer($path, $format, $duration);
    }
    
    public function ffmpeg(video_buffer $buffer, int $selected_time = 0) : void {
        $buffer->transcode_buffer->add_transcode($selected_time, 0);

        $command = "ffmpeg -i {$this->video->path_source} -c:v libx264 -preset ultrafast -crf 0";

        if ($selected_time) {
            $selected_time = date('H:i:s', $selected_time);

            if (isset($buffer->transcoded_times[$selected_time])) {
                $buffer->duration = $buffer->duration - $selected_time;
            }

            $command .= " -ss $selected_time -t $buffer->duration -async";
        }

        $command .= " {$this->video->path_output}.mp4";

        shell_exec($command);
    }
    
}