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
    verifyFilledField();
});

initLocateButtons();


// Création d'un field when click on addDestination
addDestination.addEventListener('click', function () {
    let nbChild = formDestination.childElementCount;
    if (nbChild < 13) {
        const div = document.createElement('div');
        div.classList.add('input-box');

        // const iconLeft = document.createElement('span');
        // iconLeft.classList.add('material-symbols-outlined');
        // iconLeft.textContent = 'flag';
        // div.appendChild(iconLeft);

        const dataList = document.createElement('datalist');
        dataList.id = `auto-completion-${nbChild}`;

        const input = document.createElement('input');
        input.type = 'text';
        input.placeholder = 'Commune de transition';
        input.classList.add('commune');
        input.name = `commune${nbChild}`;
        input.id = `commune${nbChild}`;
        input.setAttribute('list', dataList.id);
        input.required = true;
        input.addEventListener('input', e => {
            autocomplete(dataList, e.target.value);
        });

        div.appendChild(input);
        div.appendChild(dataList);

        const gidInput = document.createElement('input');
        gidInput.type = 'hidden';
        gidInput.name = `gid${nbChild}`;
        gidInput.id = `gid${nbChild}`;
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

        let iconEtape = document.createElement('span');
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
        verifyChild();
        verifyFilledField();
        initDeleteButtons();
        initLocateButtons();
    } else {
        addDestination.style.display = 'none';
    }
});

// Init delete button event
//when click on close class icon, remove the parent field
function initDeleteButtons() {
    for (let i = 0; i < close.length; i++) {
        close[i].onclick = function () {
            let nbChild = formDestination.childElementCount;
            if (nbChild > 2) {
                removePointOnMap(this.parentElement.children[0].name);
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
            // set all id of field
            for (let i = 0; i < formDestination.childElementCount; i++) {
                formDestination.children[i].children[0].setAttribute('id', `commune${i}`);
                formDestination.children[i].children[0].setAttribute('name', `commune${i}`);
            }
            nbField.setAttribute('value', nbChild - 1);
            verifyChild();
            verifyFilledField();
        }
    }
}

// Init locate button event
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

// add point on map according to lon and lat and remove old point if exist
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
            })
        })
    });
    map.addLayer(vectorLayer);
}

// remove point on map according to name
function removePointOnMap(name) {
    let layer = map.getLayers().getArray();
    for (let i = 0; i < layer.length; i++) {
        if (layer[i].get('name') === name) {
            map.removeLayer(layer[i]);
        }
    }
}

function verifyChild() {
    const nbChild = formDestination.childElementCount;
    const display = nbChild > 2 ? '' : 'none';
    for (let i = 0; i < close.length; i++)
        close[i].style.display = display;
}


function verifyFilledField() {
    let nbChild = formDestination.childElementCount;
    if (nbChild < 13) {
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


async function send(long, lat, target) {
    const url = 'controleurFrontal.php?controleur=noeudCommune&action=getNoeudProche&long=' + long + '&lat=' + lat;
    const response = await fetch(url);
    const data = await response.json();

    target.children[0].value = data.nom_comm + ' (' + data.departement + ')';
    target.children[2].value = data.gid;
    addPointOnMap(target.children[0].name, data.long, data.lat);
    verifyFilledField();
}
