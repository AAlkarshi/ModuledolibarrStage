CREATE TABLE llx_campagne (
	rowid integer AUTO_INCREMENT,
	entity integer DEFAULT 1,
	status integer DEFAULT 0,
	title					varchar(100),
	label					varchar(255),
	datdeb					datetime DEFAULT NULL,
	datfin					datetime DEFAULT NULL,
	datval					datetime DEFAULT NULL,
	datec                   datetime DEFAULT CURRENT_TIMESTAMP,
	tms                     timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	fk_email_template          integer      DEFAULT NULL,
	fk_user_author          integer      DEFAULT NULL,
	fk_user_modif           integer      DEFAULT NULL,
	fk_user_valid           integer      DEFAULT NULL,
	PRIMARY KEY(rowid)
)ENGINE=innodb;

ALTER TABLE llx_campagne ADD CONSTRAINT fk_campagne_email_template_rowid FOREIGN KEY (fk_email_template) REFERENCES llx_c_email_templates (rowid);
ALTER TABLE llx_campagne ADD CONSTRAINT fk_campagne_user_author_rowid FOREIGN KEY (fk_user_author) REFERENCES llx_user (rowid);
ALTER TABLE llx_campagne ADD CONSTRAINT fk_campagne_user_modif_rowid FOREIGN KEY (fk_user_modif) REFERENCES llx_user (rowid);
ALTER TABLE llx_campagne ADD CONSTRAINT fk_campagne_user_valid_rowid FOREIGN KEY (fk_user_valid) REFERENCES llx_user (rowid);
