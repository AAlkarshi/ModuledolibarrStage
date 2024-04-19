drop view llx_v_campagne_thirdparties;
CREATE VIEW llx_v_campagne_thirdparties
(
   fk_campagne,
   fk_soc,
   nom,
   name_alias,
   email,
   phone,
   url,
   address,
   zip,
   town,
   fk_departement,
   fk_pays,
   socialnetworks,
   status,
   nbclick
)
AS
   -- Sans envoi existant : on se base sur les catégories liées à la campagne
SELECT DISTINCT cc.fk_campagne, cs.fk_soc, s.nom, s.name_alias, s.email,
       s.phone, s.url, s.address, s.zip, s.town, s.fk_departement, s.fk_pays, s.socialnetworks, 
       CASE
          -- STATUS_IGNORED (999) : Pas de mail sur la société
          WHEN trim(s.email) IS NULL
          THEN
             999
          -- STATUS_STANDBY (0) : En attente d'exécution
          ELSE 0
        END as status, 0 as nbclick
FROM llx_categorie_campagne cc, llx_categorie_societe cs, llx_societe s
 WHERE     cc.fk_categorie = cs.fk_categorie
       AND cs.fk_soc = s.rowid
       AND NOT EXISTS(SELECT 1
                      FROM llx_campagne_envois cv
                     WHERE     cc.fk_campagne = cv.fk_campagne
                           AND s.email = cv.email)
       -- e-mail toujours inscrit
       AND NOT EXISTS
                   (SELECT 1
                      FROM llx_campagne_blacklist b
                     WHERE s.email = b.email)
UNION
-- Avec un envoi existant : on se base sur l'historique des envois, peu importe les liens catégories
SELECT DISTINCT cv.fk_campagne, cv.fk_soc, s.nom, s.name_alias, cv.email,
       s.phone, s.url, s.address, s.zip, s.town, s.fk_departement, s.fk_pays, s.socialnetworks,
       CASE
        -- STATUS_LOST (998) : Le contact a cliqué sur le lien pour se désinscrire à partir du mail de la campagne
        WHEN EXISTS
                (SELECT 1
                   FROM llx_campagne_blacklist cb
                  WHERE     cv.hash = cb.hash)
        THEN 998
        -- STATUS_CONSULTED (300) : Le contact a cliqué dans au moins un lien du mail
        WHEN EXISTS
                  (SELECT 1
                     FROM llx_campagne_click cl
                    WHERE cv.rowid = cl.fk_campagne_envoi)
          THEN
             300
        -- STATUT_OPENED (200) : Le contact a ouvert l'e-mail
        WHEN EXISTS
                  (SELECT 1
                     FROM llx_campagne_open co
                    WHERE cv.rowid = co.fk_campagne_envoi)
          THEN
             200
        -- STATUS_VALIDATED (100) : Mail envoyé
          WHEN cv.message = '200'
          THEN
             100
          -- STATUS_ERROR (500) : Erreur inconnue
          ELSE 500  
       END as status,
       (SELECT count(1)
          FROM llx_campagne_click k
         WHERE     cv.rowid = k.fk_campagne_envoi)
          AS nbclick
FROM llx_campagne_envois cv, llx_societe s
 WHERE  cv.fk_soc = s.rowid