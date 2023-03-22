CREATE OR REPLACE PROCEDURE CREER_UTILISATEUR(
p_login utilisateurs.login%TYPE,
p_nom utilisateurs.nom%TYPE,
p_prenom utilisateurs.prenom%TYPE,
p_mdp utilisateurs.motDePasse%TYPE,
p_email utilisateurs.email%TYPE,
p_img utilisateurs.imageProfil%TYPE) LANGUAGE plpgsql AS $$
BEGIN
    INSERT INTO nalixt.utilisateurs(login, nom, prenom, motDePasse, email, imageProfil) VALUES(p_login, p_nom, p_prenom, p_mdp, p_email, p_img);
    INSERT INTO nalixt.historique(login) VALUES(p_login);
    INSERT INTO nalixt.favoris(login) VALUES(p_login);
END; $$;


CREATE OR REPLACE PROCEDURE MODIFIER_UTILISATEUR(
p_login utilisateurs.login%TYPE,
p_nom utilisateurs.nom%TYPE,
p_prenom utilisateurs.prenom%TYPE,
p_mdp utilisateurs.motDePasse%TYPE,
p_email utilisateurs.email%TYPE,
p_img utilisateurs.imageProfil%TYPE) LANGUAGE plpgsql AS $$
BEGIN
    UPDATE nalixt.utilisateurs
    SET nom = p_nom, prenom = p_prenom, motDePasse = p_mdp, email = p_email, imageProfil = p_img
    WHERE login = p_login;
END; $$;


CREATE OR REPLACE PROCEDURE AJOUTER_HISTORIQUE(p_login nalixt.utilisateurs.login%TYPE, p_historique INTEGER) LANGUAGE plpgsql AS $$
DECLARE
    isItnull integer;
BEGIN
    SELECT COUNT(*) INTO isItnull FROM nalixt.historique WHERE login = p_login;
    IF isItnull = 0 THEN
        UPDATE nalixt.historique SET historique = ARRAY[p_historique]
        WHERE login = p_login;
    ELSE
        UPDATE nalixt.historique SET historique = array_append(historique, p_historique)
        WHERE login = p_login;
    END IF;
END; $$;


CREATE OR REPLACE PROCEDURE AJOUTER_FAVORIS(p_login nalixt.utilisateurs.login%TYPE, p_idTrajet INTEGER) LANGUAGE plpgsql AS $$
DECLARE
    isItnull integer;
    isAlreadyFavorite integer;
BEGIN
    SELECT COUNT(*) INTO isItnull FROM nalixt.favoris WHERE login = p_login;
    SELECT COUNT(*) INTO isAlreadyFavorite FROM nalixt.favoris WHERE login = p_login AND favoris @> ARRAY[p_idTrajet];
    IF isItnull = 0 THEN
        UPDATE nalixt.favoris SET favoris = ARRAY[p_idTrajet]
        WHERE login = p_login;
    ELSE
        IF isAlreadyFavorite = 0 THEN
            UPDATE nalixt.favoris SET favoris = array_append(favoris, p_idTrajet)
            WHERE login = p_login;
        END IF;
    END IF;
END; $$;


CREATE OR REPLACE PROCEDURE SUPPRIMER_FAVORIS(p_login nalixt.utilisateurs.login%TYPE, p_idTrajet INTEGER) LANGUAGE plpgsql AS $$
BEGIN
    UPDATE nalixt.favoris SET favoris = array_remove(favoris, p_idTrajet)
    WHERE login = p_login;
END; $$;


CREATE OR REPLACE PROCEDURE AJOUTER_TRAJET(p_trajets varchar) LANGUAGE plpgsql AS $$
DECLARE
    isAlreadyExisting integer;
BEGIN
    SELECT COUNT(*) INTO isAlreadyExisting FROM nalixt.trajets WHERE trajets @> ARRAY[p_trajets];
    IF isAlreadyExisting = 0 THEN
        INSERT INTO nalixt.trajets(trajets) VALUES(ARRAY[p_trajets]);
    END IF;
END; $$;