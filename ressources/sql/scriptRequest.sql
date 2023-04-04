-- 10 derniers trajets de l'historique de :login
SELECT historique.historique[(cardinality(nalixt.historique.historique)-9):cardinality(nalixt.historique.historique)] FROM historique
WHERE login = :login;

-- 10 derniers trajets de l'historique de :login avec les villes du trajet à la place des gid
SELECT idtrajet, array_agg(DISTINCT nom_comm) FROM
    (SELECT unnest(historique.historique[(cardinality(historique.historique)-9):cardinality(historique.historique)])
                as id FROM historique
     WHERE login = :login)
        as h
        JOIN nalixt.trajets t ON h.id = t.idtrajet
        JOIN unnest(t.trajets) as tt ON true
        JOIN noeud_routier nr ON (tt)::int = nr.gid
        JOIN noeud_commune nc on nr.insee_comm = nc.insee_comm
GROUP BY idtrajet;