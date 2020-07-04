create database if not exists `vstream`;

create table if not exists `vstream`.`users` (
    `id` int unsigned not null auto_increment,
    `username` varchar(255) not null unique,
    `password` varchar (256) not null,
    `salt` varchar(22) not null unique,
    constraint `users_pk` primary key (`id`)
);

create table if not exists `vstream`.`videos` (
    `id` int unsigned not null auto_increment,
    `source_path` varchar(255) not null,
    `output_path` varchar(255) not null,
    constraint `videos_pk` primary key (`id`)
);

create table if not exists `vstream`.`transcode_list` (
    `id` int unsigned not null auto_increment,
    `video_id` int unsigned,
    `interrupted_time` int unsigned not null,
    constraint `transcode_list_pk` primary key (`id`),
    constraint `videos_fk` foreign key (`video_id`) references `vstream`.`videos`(`id`)
);