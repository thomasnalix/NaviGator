-- 10 derniers trajets de l'historique de :login
SELECT historique.historique[(cardinality(historique.historique)-9):cardinality(historique.historique)] FROM historique
WHERE login = :login;

-- 10 derniers trajets de l'historique de :login avec les villes du trajet à la place des gid
SELECT idtrajet, array_agg(DISTINCT nc.nom_comm) FROM
    (SELECT unnest(historique.historique[(cardinality(historique.historique)-9):cardinality(historique.historique)])
                as id FROM historique
     WHERE login = :login)
        as h
        JOIN trajets t ON h.id = t.idtrajet
        JOIN unnest(t.trajets) as tt ON true
        JOIN noeud_commune nc ON (tt)::int = nc.gid
GROUP BY idtrajet;