const calculButton = document.getElementById('calcul');
const addDestination = document.getElementById('addDestination');
const formDestination = document.getElementById('formDestination');
const close = document.getElementsByClassName('close');
const locateButton = document.getElementsByClassName('locate-button');
const nbField = document.getElementById('nbField');
const flagBox = document.getElementById('flag-box');


// If all field input of the fox formDestination are filled, addDestination is affiched
formDestination.addEventListener('input', e => {
    e.target.parentElement.children[2].value = '';
    removePointOnMap(e.target.name);
    verifyFilledField();
});

calculButton.addEventListener("click", e => {
    const url = './noeudProche/';
    //
    // const response = await fetch(url);
    // const data = await response.json();
})

initLocateButtons();



/**
 * Add event listener on add destination button and add new field in formDestination
 */
addDestination.addEventListener('click', function () {
    let nbChild = formDestination.childElementCount;
    if (nbChild < 10) {
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
        input.addEventListener('input', e => {
            autocomplete(dataList, e.target.value);
        });

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
        verifyFilledField();
        initDeleteButtons();
        initLocateButtons();
    } else {
        addDestination.style.display = 'none';
    }
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
            verifyFilledField();
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
    for (let i = nbChild; i < numCommune; i--) {

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
                send(lon, lat, target);

                // if there is already a point according to e.target.parentElement.children[0].value, remove it and add new point
                addPointOnMap(target.children[0].name, lon, lat);
                document.body.style.cursor = 'default';
            });
        });
    }
}


/**
 * add point on map according to lon and lat and remove old point if exist
 * @param target
 * @param lon
 * @param lat
 */
function addPointOnMap(target, lon, lat) {
    let layer = map.getLayers().getArray();
    for (let i = 0; i < layer.length; i++) {
        if (layer[i].get('name') === target) {
            map.removeLayer(layer[i]);
        }
    }

    let point = new ol.geom.Point(ol.proj.fromLonLat([lon, lat]));
    let feature = new ol.Feature({
        geometry: point,
        name:target
    });
    let vectorSource = new ol.source.Vector({
        features: [feature]
    });
    let vectorLayer = new ol.layer.Vector({
        source: vectorSource,
        name: target,
        style: new ol.style.Style({
            image: new ol.style.Icon({
                anchor: [0.5, 1],
                src: '../ressources/img/map_point.png'
            }),
        })
    });
    map.addLayer(vectorLayer);
}

/**
 * Remove point on map according to name
 * @param name
 */
function removePointOnMap(name) {
    let layer = map.getLayers().getArray();
    for (let i = 0; i < layer.length; i++) {
        if (layer[i].get('name') === name) {
            map.removeLayer(layer[i]);
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
function verifyFilledField() {
    let nbChild = formDestination.childElementCount;
    if (nbChild < 8) {
        let nbChild = formDestination.childElementCount;
        let nbFilled = 0;
        for (let i = 0; i < nbChild; i++) {
            if (formDestination.children[i].children[0].value !== '')
                nbFilled++;
        }
        if (nbFilled === nbChild) {
            addDestination.style.display = 'inherit';
            calculButton.disabled = false;
        } else {
            calculButton.disabled = true;
            addDestination.style.display = 'none';
        }
    }
}


/**
 * Send request to get the nearest node
 * @param long of the clicked point
 * @param lat
 * @param target of the clicked box
 */
async function send(long, lat, target) {
    const url = './noeudProche/lon/' + long + '/lat/' + lat;
    const response = await fetch(url);
    const data = await response.json();

    target.children[0].value = data.nom_comm + ' - ' + data.departement + ' (Périphérie)';
    target.children[2].value = data.gid;
    addPointOnMap(target.children[0].name, data.long, data.lat);
    verifyFilledField();
}
