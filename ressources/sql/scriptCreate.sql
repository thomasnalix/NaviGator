CREATE TABLE nalixt.utilisateurs
(
    login      VARCHAR(50),
    nom        VARCHAR(2000) NOT NULL,
    prenom     VARCHAR(2000) NOT NULL,
    motDePasse text          NOT NULL,
    marque     VARCHAR(50),
    modele     VARCHAR(50),
    PRIMARY KEY (login)
);


CREATE TABLE nalixt.historique
(
    login      VARCHAR(20) NOT NULL,
    historique INTEGER ARRAY,
    PRIMARY KEY (login),
    FOREIGN KEY (login) REFERENCES nalixt.utilisateurs (login)
);


CREATE TABLE nalixt.trajets
(
    idTrajet SERIAL,
    trajets  VARCHAR ARRAY,
    json     jsonb,
    PRIMARY KEY (idTrajet)
);


CREATE MATERIALIZED VIEW nalixt.vitesses_route AS
SELECT nr.gid                              AS noeud_depart_gid,
       st_x(st_astext(nr.geom)::geometry)  AS noeud_depart_long,
       st_y(st_astext(nr.geom)::geometry)  AS noeud_depart_lat,
       nr2.gid                             AS noeud_arrivee_gid,
       st_x(st_astext(nr2.geom)::geometry) AS noeud_arrivee_long,
       st_y(st_astext(nr2.geom)::geometry) AS noeud_arrivee_lat,
       tr.gid                              AS troncon_gid,
       tr.longueur                         AS longueur_troncon,
       "left"(nc.insee_comm::text, 2)      AS num_departement_depart,
       "left"(nc2.insee_comm::text, 2)     AS num_departement_arrivee,
       CASE
           WHEN vocation = 'Bretelle' THEN 60
           WHEN vocation = 'Liaison locale' THEN 40
           WHEN vocation = 'Liaison principale' THEN 80
           WHEN vocation = 'Liaison régionale' THEN 70
           WHEN vocation = 'Type autoroutier' THEN 130
           END                             AS vitesse
FROM nalixt.troncon_route tr

         JOIN nalixt.noeud_routier nr ON nr.geom && st_expand(tr.geom, 0.0001::double precision) AND
                                         st_dwithin(tr.geom, nr.geom, 0.0001::double precision)
         JOIN nalixt.noeud_routier nr2 ON nr2.geom && st_expand(tr.geom, 0.0001::double precision) AND
                                          st_dwithin(tr.geom, nr2.geom, 0.0001::double precision)
         JOIN nalixt.noeud_commune nc ON nr.insee_comm::text = nc.insee_comm::text
         JOIN nalixt.noeud_commune nc2 ON nr2.insee_comm::text = nc2.insee_comm::text

WHERE nr2.gid <> nr.gid
  AND nr.gid < nr2.gid
  AND tr.sens <> 'Sens inverse';


CREATE materialized view nalixt.noeud_gid_dep AS
SELECT nc.gid, "left"(nc.insee_comm::text, 2) as num_departement
FROM nalixt.noeud_routier nc;


ALTER TABLE nalixt.noeud_routier ADD COLUMN long double precision;
ALTER TABLE nalixt.noeud_routier ADD COLUMN lat double precision;
-- Si ça ça marche pas, à executer depuis pgAdmin.
UPDATE nalixt.noeud_routier SET long = st_x(st_astext(nalixt.noeud_routier.geom)::geometry);
UPDATE nalixt.noeud_routier SET lat = st_y(st_astext(nalixt.noeud_routier.geom)::geometry);