drop database if not exists JSCData;

create database if not exists JSCData;

use JSCData;

create table if not exists locations(
	id int(11) unsigned primary key auto_increment,
	locationName varchar(100) unique not null,
	dateCreated datetime not null,
	dateModified timestamp not null on update current_timestamp
) ENGINE=InnoDB;

create table if not exists platforms(
	id int(11) unsigned primary key auto_increment,
	platformName varchar(100) unique not null,
	dateCreated datetime not null,
	dateModified timestamp not null on update current_timestamp
) ENGINE=InnoDB;

create table if not exist jsc_mentions(
	id int(11) unsigned primary key auto_increment,
	platformID int(11) unsigned not null,
	locationID int(11) unsigned not null,
	dateCreated datetime not null,
	dateModified timestamp not null on update current_timestamp
) ENGINE=InnoDB;