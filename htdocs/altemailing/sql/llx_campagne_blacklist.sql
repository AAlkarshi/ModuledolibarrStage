CREATE TABLE llx_campagne_blacklist (
	rowid integer AUTO_INCREMENT,
	email					varchar(256),
	hash					varchar(255),
	entity				integer,
	datec                   datetime DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY(rowid)
)ENGINE=innodb;