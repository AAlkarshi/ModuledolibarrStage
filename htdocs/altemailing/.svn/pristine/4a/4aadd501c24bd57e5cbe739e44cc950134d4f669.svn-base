CREATE TABLE llx_campagne_click (
	rowid integer AUTO_INCREMENT,
	fk_campagne_lien		integer,
	fk_campagne_envoi		integer,
	datec                   datetime DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY(rowid)
)ENGINE=innodb;

ALTER TABLE llx_campagne_click ADD CONSTRAINT fk_campagne_click_campagne_liens FOREIGN KEY (fk_campagne_lien) REFERENCES llx_campagne_liens (rowid);
ALTER TABLE llx_campagne_click ADD CONSTRAINT fk_campagne_click_campagne_envoi FOREIGN KEY (fk_campagne_envoi) REFERENCES llx_campagne_envois (rowid);