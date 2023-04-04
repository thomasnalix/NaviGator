DROP TABLE nalixt.trajets;
DROP TABLE nalixt.historique;
DROP TABLE nalixt.favoris;
DROP TABLE nalixt.utilisateurs;

DROP PROCEDURE nalixt.ajouter_favoris;
DROP PROCEDURE nalixt.supprimer_favoris;
DROP PROCEDURE nalixt.ajouter_trajet;
DROP PROCEDURE nalixt.ajouter_historique;
DROP PROCEDURE nalixt.creer_utilisateur;
DROP PROCEDURE nalixt.modifier_utilisateur;
DROP PROCEDURE nalixt.supprimer_utilisateur;

DROP MATERIALIZED VIEW nalixt.noeuds_from_troncon;
DROP MATERIALIZED VIEW nalixt.noeud_gid_dep;