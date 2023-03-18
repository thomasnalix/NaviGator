<div>
    <form method="<?= $method ?>" action="controleurFrontal.php">
        <fieldset>
            <legend>Mon formulaire :</legend>
            <p class="InputAddOn">
                <label class="InputAddOn-item" for="login_id">Login&#42;</label>
                <input class="InputAddOn-field" type="text" value="" placeholder="Ex : rlebreton" name="login" id="login_id" required>
            </p>
            <p class="InputAddOn">
                <label class="InputAddOn-item" for="prenom_id">Prenom&#42;</label>
                <input class="InputAddOn-field" type="text" value="" placeholder="Ex : Romain" name="prenom" id="prenom_id" required>
            </p>
            <p class="InputAddOn">
                <label class="InputAddOn-item" for="nom_id">Nom&#42;</label>
                <input class="InputAddOn-field" type="text" value="" placeholder="Ex : Lebreton" name="nom" id="nom_id" required>
            </p>
            <p class="InputAddOn">
                <label class="InputAddOn-item" for="email_id">Email&#42;</label>
                <input class="InputAddOn-field" type="email" value="" placeholder="rlebreton@yopmail.com" name="email" id="email_id" required>
            </p>
            <p class="InputAddOn">
                <label class="InputAddOn-item" for="mdp_id">Mot de passe&#42;</label>
                <input class="InputAddOn-field" type="password" value="" placeholder="" name="mdp" id="mdp_id" required>
            </p>
            <p class="InputAddOn">
                <label class="InputAddOn-item" for="mdp2_id">Vérification du mot de passe&#42;</label>
                <input class="InputAddOn-field" type="password" value="" placeholder="" name="mdp2" id="mdp2_id" required>
            </p>
            <p class="InputAddOn">
                <label class="InputAddOn-item" for="profil_id">Photo de profil&#42;</label>
                <input class="InputAddOn-field" accept="image/png, image/jpeg" type="file" name="imageProfil" id="profil_id" required>
            </p>

            <input type='hidden' name='action' value='creerDepuisFormulaire'>
            <input type='hidden' name='controleur' value='utilisateur'>
            <p class="InputAddOn">
                <input class="InputAddOn-field" type="submit" value="Envoyer" />
            </p>
        </fieldset>
    </form>
</div>