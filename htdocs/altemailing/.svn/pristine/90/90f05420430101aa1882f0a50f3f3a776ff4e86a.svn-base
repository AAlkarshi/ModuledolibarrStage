create table llx_categorie_campagne
(
  fk_categorie  integer NOT NULL,
  fk_campagne    	integer NOT NULL,
  import_key    varchar(14)
)ENGINE=innodb;


ALTER TABLE llx_categorie_campagne ADD PRIMARY KEY pk_categorie_campagne (fk_categorie, fk_campagne);
ALTER TABLE llx_categorie_campagne ADD INDEX idx_categorie_campagne_fk_categorie (fk_categorie);
ALTER TABLE llx_categorie_campagne ADD INDEX idx_categorie_campagne_fk_campagne (fk_campagne);

ALTER TABLE llx_categorie_campagne ADD CONSTRAINT fk_categorie_campagne_categorie_rowid FOREIGN KEY (fk_categorie) REFERENCES llx_categorie (rowid);
ALTER TABLE llx_categorie_campagne ADD CONSTRAINT fk_categorie_campagne_fk_soc   FOREIGN KEY (fk_campagne) REFERENCES llx_campagne (rowid);
