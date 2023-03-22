CREATE OR REPLACE PROCEDURE CREER_UTILISATEUR(
p_login utilisateurs.login%TYPE,
p_nom utilisateurs.nom%TYPE,
p_prenom utilisateurs.prenom%TYPE,
p_mdp utilisateurs.motDePasse%TYPE,
p_email utilisateurs.email%TYPE,
p_img utilisateurs.imageProfil%TYPE) LANGUAGE plpgsql AS $$
BEGIN
    INSERT INTO utilisateurs(login, nom, prenom, motDePasse, email, imageProfil) VALUES(p_login, p_nom, p_prenom, p_mdp, p_email, p_img);
    INSERT INTO historique(login) VALUES(p_login);
END; $$;


CREATE OR REPLACE PROCEDURE MODIFIER_UTILISATEUR(
p_login utilisateurs.login%TYPE,
p_nom utilisateurs.nom%TYPE,
p_prenom utilisateurs.prenom%TYPE,
p_mdp utilisateurs.motDePasse%TYPE,
p_email utilisateurs.email%TYPE,
p_img utilisateurs.imageProfil%TYPE) LANGUAGE plpgsql AS $$
BEGIN
    UPDATE utilisateurs
    SET nom = p_nom, prenom = p_prenom, motDePasse = p_mdp, email = p_email, imageProfil = p_img
    WHERE login = p_login;
END; $$;


CREATE OR REPLACE PROCEDURE AJOUTER_HISTORIQUE(p_login utilisateurs.login%TYPE, p_historique varchar) LANGUAGE plpgsql AS $$
DECLARE
    isItnull integer;
BEGIN
    SELECT COUNT(*) INTO isItnull FROM historique WHERE login = p_login;
    IF isItnull = 0 THEN
        UPDATE historique SET historique = ARRAY[p_historique]
        WHERE login = p_login;
    ELSE
        UPDATE historique SET historique = array_append(historique, p_historique)
        WHERE login = p_login;
    END IF;
END; $$;


CREATE OR REPLACE PROCEDURE AJOUTER_HISTORIQUE_2(p_login utilisateurs.login%TYPE, p_historique varchar) LANGUAGE plpgsql AS $$
BEGIN
    INSERT INTO historique_2(login, historique) VALUES(p_login, ARRAY[p_historique]);
END; $$;