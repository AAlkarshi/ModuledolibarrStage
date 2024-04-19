CREATE TABLE llx_campagne_open (
	rowid integer AUTO_INCREMENT,
	fk_campagne_envoi		integer,
	datec                   datetime DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY(rowid)
)ENGINE=innodb;

ALTER TABLE llx_campagne_open ADD CONSTRAINT fk_campagne_open_campagne_envoi FOREIGN KEY (fk_campagne_envoi) REFERENCES llx_campagne_envois (rowid);