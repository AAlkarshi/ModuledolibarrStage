CREATE TABLE llx_campagne_envois (
	rowid integer AUTO_INCREMENT,
	email					varchar(256),
	hash					varchar(255),
	sendid					varchar(255),
	message					varchar(500),
	fk_campagne				integer,
	fk_soc				integer,
	datec                   datetime DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY(rowid)
)ENGINE=innodb;


ALTER TABLE llx_campagne_envois add open integer default 0;
ALTER TABLE llx_campagne_envois ADD CONSTRAINT fk_campagne_campagne_envois FOREIGN KEY (fk_campagne) REFERENCES llx_campagne (rowid);
ALTER TABLE llx_campagne_envois ADD CONSTRAINT fk_soc_campagne_envois FOREIGN KEY (fk_soc) REFERENCES llx_societe (rowid);
