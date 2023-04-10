-- NOEUD COMMUNE
create unique index noeud_commune_pk
    on nalixt.noeud_commune (gid);

create index noeud_commune_insee_index
    on nalixt.noeud_commune (insee_comm);

create index noeud_commune_nom_comm_index
    on nalixt.noeud_commune (nom_comm);


-- NOEUD ROUTIER
create unique index noeud_routier_pk
    on nalixt.noeud_routier (gid);

create index noeud_routier_id_rte500_index
    on nalixt.noeud_routier (id_rte500);

create index noeud_routier_idx_geom
    on nalixt.noeud_routier using gist (geom);

create index noeud_routier_insee_index
    on nalixt.noeud_routier (insee_comm);


-- TRONCON ROUTE
create unique index troncon_route_pk
    on nalixt.troncon_route (gid);

create unique index troncon_route_geom_idx
    on nalixt.troncon_route using gist (geom);


-- VUE NOEUDS_FROM_TRONCON
create index noeuds_from_troncon_idx_departement_depart
    on nalixt.noeuds_from_troncon (num_departement_depart);

create index noeuds_from_troncon_idx_departement_arrivee
    on nalixt.noeuds_from_troncon (num_departement_arrivee);

create index noeuds_from_troncon_idx_depart_gid
    on nalixt.noeuds_from_troncon (noeud_depart_gid);

create index noeuds_from_troncon_idx_arrivee_gid
    on nalixt.noeuds_from_troncon (noeud_arrivee_gid);


-- VUE VITESSES
create index vitesses_route_idx_departement_depart
    on nalixt.vitesses_route (num_departement_depart);

create index vitesses_route_idx_departement_arrivee
    on nalixt.vitesses_route (num_departement_arrivee);


-- VUE NOEUD_GID_DEP
create index noeuds_from_troncon_idx_gid
    on nalixt.noeud_gid_dep (gid);