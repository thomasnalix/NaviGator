create table noeud_commune
(
    gid        serial
        constraint noeud_commune_pk
            primary key,
    id_rte500  varchar(24),
    insee_comm varchar(5),
    nom_chf    varchar(200),
    statut     varchar(30),
    nom_comm   varchar(100),
    superficie double precision,
    population double precision,
    id_nd_rte  varchar(24),
    geom       geometry
);

create index noeud_commune_nom_comm_index
    on noeud_commune (nom_comm);

create index noeud_commune_insee_index
    on noeud_commune (insee_comm);



create table noeud_routier
(
    gid        integer not null
        constraint noeud_routier_pk
            primary key,
    id_rte500  varchar(24),
    nature     varchar(80),
    insee_comm varchar(5),
    geom       geometry,
    long       double precision,
    lat        double precision
);

create index noeud_routier_id_rte500_index
    on noeud_routier (id_rte500);

create index noeud_routier_idx_geom
    on noeud_routier using gist (geom);

create index noeud_routier_insee_index
    on noeud_routier (insee_comm);




create table troncon_route
(
    gid        serial
        constraint troncon_route_pk
            primary key,
    id_rte500  varchar(24),
    vocation   varchar(80),
    nb_chausse varchar(80),
    nb_voies   varchar(80),
    etat       varchar(80),
    acces      varchar(80),
    res_vert   varchar(80),
    sens       varchar(80),
    num_route  varchar(24),
    res_europe varchar(200),
    longueur   double precision,
    class_adm  varchar(20),
    geom       geometry
);

alter table troncon_route
    owner to postgres;

create index troncon_route_geom_idx
    on troncon_route using gist (geom);