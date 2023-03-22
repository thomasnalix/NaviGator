DROP TABLE utilisateurs;
DROP TABLE trajets;
DROP TABLE historique;
DROP TABLE favoris;

DROP PROCEDURE ajouter_favoris;
DROP PROCEDURE supprimer_favoris;
DROP PROCEDURE ajouter_trajet;
DROP PROCEDURE ajouter_historique;
DROP PROCEDURE ajouter_utilisateur;
DROP PROCEDURE modifier_utilisateur;

DROP MATERIALIZED VIEW noeuds_from_troncon;
DROP MATERIALIZED VIEW noeud_gid_dep;