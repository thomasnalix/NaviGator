const navBox = document.getElementById('nav-box');
const calculButton = document.getElementById('calcul');
const addDestination = document.getElementById('addDestination');
const formDestination = document.getElementById('formDestination');
const close = document.getElementsByClassName('close');
const locateButton = document.getElementsByClassName('locate-button');
const nbField = document.getElementById('nbField');
const flagBox = document.getElementById('flag-box');
const result = document.getElementById('result');
const form = document.getElementById('form');

initLocateButtons();
verifyChild();

function debounce(callback, wait) {
    let timerId;
    return (...args) => {
        clearTimeout(timerId);
        timerId = setTimeout(() => {
            callback(...args);
        }, wait);
    };
}

// If all field input of the fox formDestination are filled, addDestination is affiched
formDestination.addEventListener('input', e => {
    e.target.parentElement.children[2].value = '';
    removePointOnMap(e.target.name);
    changeAddStepButton();
});


form.addEventListener("submit", async e => {
    e.preventDefault();
    const url = './calculChemin';
    const formData = new FormData();
    const nbChild = formDestination.childElementCount;
    for (let i = 0; i < nbChild; i++) {
        formData.append(`commune${i}`, formDestination.children[i].children[0].value);
        formData.append(`gid${i}`, formDestination.children[i].children[2].value);
    }
    formData.append('nbField', nbField.value);
    toggleLoading(true);
    const path = fetch(url, {method: 'POST', body: formData})
        .then(response => response.json());

    const car = fetch('./voiture', {method: 'GET'})
        .then(response => response.json())
        .then(carData => getFirstCar({make: carData.marque, model: carData.modele}))
        .catch(() => undefined);

    const [pathData, carData] = await Promise.all([path, car]);
    toggleLoading(false);
    console.log(pathData);
    printResult(pathData, carData);
    if (pathData.distance !== -1) {
        printItinary(pathData.chemin);
        for (let i = 0; i < nbChild; i++) {
            let field = formDestination.children[i].children[0];
            if (!field.value.match(/\s\(\w+\d+\)/) &&
                !field.value.match(/Périphérie/))
                placePoint(field.value, field.id);
        }
        await addToHistory(pathData);
    }
});

/** toggle button and input field with disable status or not and depending on the boolean value
 * @param type
 */
function toggleLoading(type) {
    let loader = document.getElementById('load');
    if (type) {
        loader.style.display = 'initial';
    } else {
        loader.style.display = 'none';
    }
    calculButton.disabled = type;
    for (let i = 0; i < formDestination.childElementCount; i++)
        formDestination.children[i].children[0].disabled = type;

}

/**
 * When redirecting from the history page, the map is loaded with the data of the selected path
 */
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
    for (let i = 0; i < trajet.noeudsList.length; i++)
        placePointByGid(trajet.noeudsList[i].nomCommune, trajet.noeudsList[i].gid, "commune" + i);

    // set all the fields with the data of the selected path
    if (trajet.noeudsList.length > 2) {
        formDestination.children[0].children[0].value = "Chargement...";
        formDestination.children[1].children[0].value = "Chargement...";
        for (let i = 0; i < trajet.noeudsList.length - 2; i++) {
            addField();
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

/**
 * Place a point on the map by requesting the server with a gid and not a city name
 * @param nomCommune
 * @param gid
 * @param id
 */
function placePointByGid(nomCommune,gid = "", id) {
    // should send a request to the server to get the coordinates of the city and place a marker on the map
    const url = './communes/coord/'+ gid;
    fetch(url)
        .then(response => response.json())
        .then(data => {
            addPointOnMap(id, data.long, data.lat, nomCommune);
        })
        .catch(error => console.log(error));
}

/**
 * Send data to the server to add it to the history of the user
 */
async function addToHistory(data) {
    const url = './historique';
    const formData = new FormData();

    formData.append('datas', JSON.stringify(data));
    formData.append('noeudsList', data.noeudsList.toString());

    await fetch(url, {method: 'POST', body: formData});
}

/**
 * set variables in the resume box
 * @param pathData
 * @param carData the car data from the API (can be undefined)
 */
function printResult(pathData, carData) {
    result.style.display = 'flex';
    const infoField = document.getElementById('info');
    const timeField = document.getElementById('time-field');
    const distanceField = document.getElementById('distance-field');
    const gasField = document.getElementById('gas-field');

    if (pathData.distance === -1) {
        distanceField.textContent = "-km";
        timeField.textContent = "-h";
        gasField.textContent = "-L";
        infoField.textContent = "Aucun itinéraire n'a été trouvé.";
    } else {
        timeField.textContent = ((jours = Math.floor(pathData.temps / 24)) > 0 ? jours + 'j ' : '') + ((heures = Math.floor(pathData.temps % 24)) > 0 ? heures + 'h ' : '') + Math.round((pathData.temps - Math.floor(pathData.temps)) * 60) + 'm';

        const consumption = getFuelConsumption(carData, pathData.distance)
        if (consumption < 0) {
            gasField.textContent = (consumption * -1) + " L";
            infoField.textContent = "Vous n'avez pas renseigné de voiture, nous avons donc utilisé une voiture moyenne pour calculer votre consommation de carburant.";
        } else {
            gasField.textContent = consumption;
            infoField.textContent = "Calcul de la consommation de carburant effectué avec votre voiture.";
        }

        // crop the distance to 2 decimals
        distanceField.textContent = pathData.distance.toFixed(2) + ' km';
    }
}

/* When the user move the map, the navBox is hidden */
map.on('pointerdrag', function () {
    navBox.style.display = 'none';
});

/* When the user stop moving the map, the navBox is shown */
map.on('pointerup', function () {
    navBox.style.display = 'flex';
});


/**
 * Add event listener on add destination button and add new field in formDestination
 */
addDestination.addEventListener('click', addField);

function addField() {
    let nbChild = formDestination.childElementCount;
    if (nbChild < 10 && verifyFillField()) {
        const div = document.createElement('div');
        div.classList.add('input-box');

        const dataList = document.createElement('datalist');
        dataList.id = `auto-completion-${nbChild - 1}`;

        const input = document.createElement('input');
        input.type = 'text';
        input.placeholder = 'Commune de transition';
        input.classList.add('commune');
        input.name = `commune${nbChild - 1}`;
        input.id = `commune${nbChild - 1}`;
        input.setAttribute('list', dataList.id);
        input.required = true;
        input.addEventListener('input', debounce(e => autocomplete(input.list, e.target.value), 200));
        input.oninput = e => checkForValidInput(e.target);

        div.appendChild(input);
        div.appendChild(dataList);

        const gidInput = document.createElement('input');
        gidInput.type = 'hidden';
        gidInput.name = `gid${nbChild - 1}`;
        gidInput.id = `gid${nbChild - 1}`;
        div.appendChild(gidInput);

        const iconRight = document.createElement('span');
        iconRight.classList.add('material-symbols-outlined', 'locate-button');
        iconRight.textContent = 'my_location';
        div.appendChild(iconRight);

        const iconDelete = document.createElement('span');
        iconDelete.classList.add('material-symbols-outlined', 'close');
        iconDelete.textContent = 'close';
        div.appendChild(iconDelete);


        // if nbItem = 2, add more point
        if (nbChild === 2) {
            for (let i = 0; i < 2; i++) {
                let point = document.createElement('span');
                point.classList.add('point');
                // append child end - 1
                flagBox.insertBefore(point, flagBox.children[flagBox.childElementCount - 1]);
            }
        }

        const iconEtape = document.createElement('span');
        iconEtape.classList.add('material-symbols-outlined', 'etape');
        iconEtape.textContent = 'fiber_manual_record';
        // append child end - 1
        flagBox.insertBefore(iconEtape, flagBox.children[flagBox.childElementCount - 1]);

        for (let i = 0; i < 2; i++) {
            let point = document.createElement('span');
            point.classList.add('point');
            // append child end - 1
            flagBox.insertBefore(point, flagBox.children[flagBox.childElementCount - 1]);
        }


        // add new field in formDestination before end - 1
        formDestination.insertBefore(div, formDestination.children[formDestination.childElementCount - 1]);
        nbField.setAttribute('value', nbChild + 1);
        updateWhenAdd(nbChild - 1)
        verifyChild();
        updateIdInput();
        changeAddStepButton();
        initDeleteButtons();
        initLocateButtons();
    } else {
        addDestination.classList.add('disabled');
    }
}

/**
 * Init delete button event listener
 * When click on close class icon, remove the parent field
 */
function initDeleteButtons() {
    for (let i = 0; i < close.length; i++) {
        close[i].onclick = function () {
            let nbChild = formDestination.childElementCount;
            if (nbChild > 2) {
                removePointOnMap(this.parentElement.children[0].name);
                updateWhenDelete(this.parentElement.children[0].name)
                this.parentElement.remove();
                // remove point last - 1 point in flagBox
                for (let i = 0; i < 3; i++) {
                    flagBox.children[flagBox.childElementCount - 2].remove();
                }
                if (nbChild === 3) {
                    flagBox.children[flagBox.childElementCount - 2].remove();
                    flagBox.children[flagBox.childElementCount - 2].remove();
                }
            }
            nbField.setAttribute('value', nbChild - 1);
            // set all id of field
            updateIdInput(false);
            verifyChild();
            changeAddStepButton();
        }
    }
}

/**
 * Update all id of children of input-box and point on map
 */
function updateIdInput(add = true) {
    for (let i = 0; i < formDestination.childElementCount; i++) {
        formDestination.children[i].children[0].setAttribute('id', `commune${i}`);
        formDestination.children[i].children[0].setAttribute('name', `commune${i}`);
        formDestination.children[i].children[0].setAttribute('list', `auto-completion-${i}`);
        formDestination.children[i].children[1].setAttribute('id', `auto-completion-${i}`);
        formDestination.children[i].children[2].setAttribute('id', `gid${i}`);
        formDestination.children[i].children[2].setAttribute('name', `gid${i}`);
    }
}

/**
 * When delete a field, update the name of all layer on map
 * @param nomCommune
 */
function updateWhenDelete(nomCommune) {
    let layer = map.getLayers().getArray();
    let numCommune = Number(nomCommune);
    let nbChild = formDestination.childElementCount;
    for (let i = numCommune; i < nbChild - 1; i++) {
        if (layer.find(layer => layer.get('name') === `commune${i}`)) {
            layer[i].set('name', `commune${i}`);
        }
    }
}

/**
 * When add a new field, update the name of all layer on map
 * @param nomCommune
 */
function updateWhenAdd(nomCommune) {
    let layer = map.getLayers().getArray();
    let numCommune = Number(nomCommune);
    let nbChild = formDestination.childElementCount;
    for (let i = numCommune; i < nbChild - 1; i++) {
        if (layer.find(layer => layer.get('name') === `commune${i}`)) {
            layer.find(layer => layer.get('name') === `commune${i}`).set('name', `commune${i + 1}`);
        }
    }
}


/**
 * Init the event listener of all locate button
 * When click on locate button, wait for the user to click on the map
 * Then send the coordinates to the server
 * And add a point on the map
 */
function initLocateButtons() {
    for (let i = 0; i < locateButton.length; i++) {
        locateButton[i].addEventListener('click', e => {
            // wait for the user to click on the map
            document.body.style.cursor = 'crosshair';

            map.once('click', function (evt) {
                let coord = ol.proj.transform(evt.coordinate, 'EPSG:3857', 'EPSG:4326');
                let lon = coord[0];
                let lat = coord[1];
                let target = e.target.parentElement;
                getNearestNode(lon, lat, target);

                // if there is already a point according to e.target.parentElement.children[0].value, remove it and add new point
                addPointOnMap(target.children[0].name, lon, lat);
                document.body.style.cursor = 'default';
            });
        });
    }
}


/**
 * If there is more than 2 field, display the close icon
 */
function verifyChild() {
    const nbChild = formDestination.childElementCount;
    const display = nbChild > 2 ? '' : 'none';
    for (let i = 0; i < close.length; i++)
        close[i].style.display = display;
}

/**
 * Verify if all field are filled and if yes, display the button to add a destination
 */
function changeAddStepButton() {
    let nbChild = formDestination.childElementCount;
    if (nbChild < 8) {
        let nbChild = formDestination.childElementCount;
        let nbFilled = 0;
        for (let i = 0; i < nbChild; i++) {
            if (formDestination.children[i].children[0].value !== '')
                nbFilled++;
        }
        if (nbFilled === nbChild) {
            addDestination.classList.remove('disabled');
            //addDestination.style.display = 'inherit';
            calculButton.disabled = false;
        } else {
            addDestination.classList.add('disabled');
            calculButton.disabled = true;
            //addDestination.style.display = 'none';
        }
    }
}

function verifyFillField() {
    let nbChild = formDestination.childElementCount;
    let nbFilled = 0;
    for (let i = 0; i < nbChild; i++) {
        if (formDestination.children[i].children[0].value !== '')
            nbFilled++;
    }
    return nbFilled === nbChild;
}

/**
 * Send request to get the nearest node
 * @param long of the clicked point
 * @param lat
 * @param target of the clicked box
 */
async function getNearestNode(long, lat, target) {
    const url = './noeudProche/lon/' + long + '/lat/' + lat;
    const response = await fetch(url);
    const data = await response.json();

    target.children[0].value = data.nom_comm + ' - ' + data.departement + ' (Périphérie)';
    target.children[2].value = data.gid;
    addPointOnMap(target.children[0].name, data.long, data.lat, data.nom_comm);
    changeAddStepButton();
}

