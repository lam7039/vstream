create database if not exists `vstream`;

create table if not exists `vstream`.`users` (
    `id` int unsigned not null auto_increment,
    `username` varchar(60) not null unique,
    `password` varchar (255) not null,
    `ip_address` int(45) unsigned not null unique,
    constraint `users_pk` primary key (`id`)
);

create table if not exists `vstream`.`media` (
    `id` int unsigned not null auto_increment,
    `source_path` varchar(255) not null,
    `output_path` varchar(255) not null,
    constraint `videos_pk` primary key (`id`)
);

create table if not exists `vstream`.`scheduled_jobs` (
    `id` int unsigned not null auto_increment,
    `at` datetime not null,
    `media_id` int unsigned not null,
    `locked` unsigned tinyint(1) not null default 0,
    `status` varchar(255) not null default 0,
    `response` text null,
    constraint `scheduled_jobs_pk` primary key (`id`),
    constraint `media_fk` foreign key (`media_id`) references `vstream`.`media`(`id`) on delete cascade
);