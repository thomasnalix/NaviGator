const navBox = document.getElementById('nav-box');
const calculButton = document.getElementById('calcul');
const formDestination = document.getElementById('formDestination');
const close = document.getElementsByClassName('close');
const nbField = document.getElementById('nbField');
const flagBox = document.getElementById('flag-box');
const result = document.getElementById('result');
const form = document.getElementById('form');
const addDestination = document.getElementById('addDestination');

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
    let loader = document.getElementById('load');
    const url = './calculChemin';
    const formData = new FormData();
    const nbChild = formDestination.childElementCount;
    for (let i = 0; i < nbChild; i++) {
        formData.append(`commune${i}`, formDestination.children[i].children[0].value);
        formData.append(`gid${i}`, formDestination.children[i].children[2].value);
    }
    formData.append('nbField', nbField.value);

    loader.style.display = 'initial';
    // lock the button
    calculButton.disabled = true;

    const path = fetch(url, {method: 'POST', body: formData})
    const gas = fetch('./calculConsommation', {method: 'GET'})

    const [pathResponse, gasResponse] = await Promise.all([path, gas]);
    const [pathData, gasData] = await Promise.all([pathResponse.json(), gasResponse.json()]);
    loader.style.display = 'none';
    calculButton.disabled = false;
    printResult(pathData, gasData);
    printItinary(pathData.chemin);
    await addToHistory(pathData);
});

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
 * @param data
 */
function printResult(data) {
    result.style.display = 'initial';
    let resumeField = document.getElementById('resume-field');
    let timeField = document.getElementById('time-field');
    let distanceField = document.getElementById('distance-field');
    let gasField = document.getElementById('gas-field');
    let nbStep = ((data.nbCommunes) - 2);
    let etapesString = nbStep !== 0 ? ' (via ' + nbStep + ' étape' + (nbStep !== 1 ? 's)' : ')') : '';
    resumeField.textContent = data.nomCommuneDepart + ' vers ' + data.nomCommuneArrivee + etapesString;
    timeField.textContent = Math.floor(data.temps) + 'h' + Math.round((data.temps - Math.floor(data.temps)) * 60);
    //gasField.textContent = data.gas.toFixed(2) + ' L';
    // TODO: avec api
    // crop the distance to 2 decimals
    distanceField.textContent = data.distance.toFixed(2) + ' km';
}


map.on('pointerdrag', function () {
    navBox.style.display = 'none';
});

map.on('pointerup', function () {
    navBox.style.display = 'flex';
});

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
        //    buttonNumber -1;
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

function verifyFillField()  {
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

