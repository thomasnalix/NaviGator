import {addStep, idData} from "./addStep.js";

window.addEventListener('load', async function () {
    const trajet = JSON.parse(localStorage.getItem('trajet'));
    if (trajet === null) return;
    toggleLoading(true);
    const response = await fetch('./voiture', {method: 'GET'});
    const datasReponse = await response.json();
    const carData = await getFirstCar({make: datasReponse.marque, model: datasReponse.modele});

    toggleLoading(false);
    printResult(trajet, carData);
    printItinary(trajet.chemin);

    // Ajout des deux derniers points sur les emplacements 1 et 2 (par défaut)
    placePointByGid(trajet.noeudsList[0].nomCommune, trajet.noeudsList[0].gid, 1);
    placePointByGid(trajet.noeudsList[trajet.noeudsList.length - 1].nomCommune, trajet.noeudsList[trajet.noeudsList.length - 1].gid, 2);

    for (let i = 1; i < trajet.noeudsList.length-1; i++) placePointByGid(trajet.noeudsList[i].nomCommune, trajet.noeudsList[i].gid, i + 2);

    // set all the fields with the data of the selected path
    if (trajet.noeudsList.length > 2) {
        formDestination.children[0].children[0].value = "Chargement...";
        formDestination.children[1].children[0].value = "Chargement...";
        for (let i = 0; i < trajet.noeudsList.length - 2; i++) {
            addStep();
            formDestination.children[i + 1].children[0].value = "Chargement...";
        }
    }
    for (let i = 0; i < trajet.noeudsList.length; i++) {
        let field = formDestination.children[i].children[0];
        field.value = trajet.noeudsList[i].nomCommune;
        field.parentElement.children[2].value = trajet.noeudsList[i].gid;
    }
    localStorage.removeItem('trajet');
});