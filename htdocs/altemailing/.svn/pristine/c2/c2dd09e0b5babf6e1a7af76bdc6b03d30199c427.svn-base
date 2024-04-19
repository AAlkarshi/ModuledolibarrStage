drop view llx_v_campagne_open;

CREATE VIEW llx_v_campagne_open(rowid, dateo, email, fk_soc, fk_campagne, datenvoi) AS
select o.rowid, o.datec, v.email, v.fk_soc, v.fk_campagne, v.datec
from llx_campagne_open o
, llx_campagne_envois v
where o.fk_campagne_envoi = v.rowid