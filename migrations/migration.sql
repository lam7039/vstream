create database if not exists `vstream`;

create table if not exists `vstream`.`users` (
    `id` int unsigned not null auto_increment,
    `username` varchar(60) not null unique,
    `password` varchar (255) not null,
    `ip_address` int(45) unsigned not null unique,
    `expiry` datetime not null,
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
    constraint `videos_fk` foreign key (`video_id`) references `vstream`.`videos`(`id`) on delete cascade
);

drop trigger if exists `users_expiry_update`;

create trigger `users_expiry_update`
    before update on `vstream`.`users`
    for each row
    set new.`expiry` = adddate(now(), interval 10 day);