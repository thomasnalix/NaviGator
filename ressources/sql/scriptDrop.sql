DROP TABLE trajets;
DROP TABLE historique;
DROP TABLE favoris;
DROP TABLE utilisateurs;

DROP PROCEDURE ajouter_favoris;
DROP PROCEDURE supprimer_favoris;
DROP PROCEDURE ajouter_trajet;
DROP PROCEDURE ajouter_historique;
DROP PROCEDURE creer_utilisateur;
DROP PROCEDURE modifier_utilisateur;
DROP PROCEDURE supprimer_utilisateur;

DROP MATERIALIZED VIEW noeuds_from_troncon;
DROP MATERIALIZED VIEW noeud_gid_dep;