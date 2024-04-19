CREATE TABLE llx_campagne_liens (
	rowid integer AUTO_INCREMENT,
	url					varchar(300),
	code					varchar(12),
	fk_campagne				integer,
	fk_user_author          integer      DEFAULT NULL,
	fk_user_modif           integer      DEFAULT NULL,
	datec                   datetime DEFAULT CURRENT_TIMESTAMP,
	tms                     timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	PRIMARY KEY(rowid)
)ENGINE=innodb;

ALTER TABLE llx_campagne_liens ADD CONSTRAINT fk_campagne_campagne_liens FOREIGN KEY (fk_campagne) REFERENCES llx_campagne (rowid);
ALTER TABLE llx_campagne_liens ADD CONSTRAINT fk_user_author_campagne_liens FOREIGN KEY (fk_user_author) REFERENCES llx_user (rowid);
ALTER TABLE llx_campagne_liens ADD CONSTRAINT fk_user_modif_campagne_liens FOREIGN KEY (fk_user_modif) REFERENCES llx_user (rowid);