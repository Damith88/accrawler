create table `article` (
    `id` int(10) not null auto_increment,
    `heading` varchar(200) not null,
    `url` varchar(200) not null,
    `content` longtext not null,
    `date` date not null,
    `category_id` int(5) default null,
    `additional_info` varchar(1000) default null,
    primary key (`id`)
) engine=innodb default charset=utf8;


create table `tag` (
    `id` int(5) not null auto_increment,
    `name` varchar(100) not null unique,
    primary key (`id`)
) engine=innodb default charset=utf8;

create table `category` (
    `id` int(5) not null auto_increment,
    `name` varchar(100) not null unique,
    primary key (`id`)
) engine=innodb default charset=utf8;