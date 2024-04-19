DROP VIEW llx_v_campagne_detail_clics;

CREATE VIEW llx_v_campagne_detail_clics
(
   id,
   datclic,
   fk_campagne_lien,
   code,
   url,
   datenvoi,
   fk_campagne,
   fk_soc,
   email,
   nom,
   name_alias,
   phone,
   siteweb,
   address,
   zip,
   town,
   fk_departement,
   fk_pays,
   socialnetworks
)
AS
select k.rowid, k.datec as datclic, k.fk_campagne_lien, l.code, k.fk_campagne_lien,
v.datec as datenvoi, v.fk_campagne, v.fk_soc, v.email,
                   s.nom,
                   s.name_alias,
                   s.phone,
                   s.url,
                   s.address,
                   s.zip,
                   s.town,
                   s.fk_departement,
                   s.fk_pays,
                   s.socialnetworks
from llx_campagne_click k, llx_campagne_liens l
, llx_campagne_envois v
, llx_societe s
where k.fk_campagne_lien = l.rowid
and k.fk_campagne_envoi = v.rowid
and v.fk_soc = s.rowid;