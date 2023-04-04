-- 10 derniers trajets de l'historique de :login
SELECT historique.historique[(cardinality(historique.historique)-9):cardinality(historique.historique)] FROM historique
WHERE login = :login;

