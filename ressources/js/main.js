const calculButton = document.getElementById('calcul');
const addDestination = document.getElementById('addDestination');
const formDestination = document.getElementById('formDestination');
const close = document.getElementsByClassName('close');
const locateButton = document.getElementsByClassName('locate-button');
const nbField = document.getElementById('nbField');


// If all field input of the fox formDestination are filled, addDestination is affiched
formDestination.addEventListener('input', e => {
    e.target.parentElement.children[3].value = '';
    verifyFilledField();
});

initLocateButtons();


// Création d'un field when click on addDestination
addDestination.addEventListener('click', function () {
    let nbChild = formDestination.childElementCount;
    if (nbChild < 13) {
        const div = document.createElement('div');
        div.classList.add('input-box');

        const iconLeft = document.createElement('span');
        iconLeft.classList.add('material-symbols-outlined');
        iconLeft.textContent = 'flag';
        div.appendChild(iconLeft);

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
        iconRight.textContent = 'point_scan';
        div.appendChild(iconRight);

        const iconDelete = document.createElement('span');
        iconDelete.classList.add('material-symbols-outlined', 'close');
        iconDelete.textContent = 'close';
        div.appendChild(iconDelete);


        formDestination.appendChild(div);
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
                this.parentElement.remove();
            }
            // set all id of field
            for (let i = 0; i < formDestination.childElementCount; i++) {
                formDestination.children[i].children[1].setAttribute('id', `commune${i}`);
                formDestination.children[i].children[1].setAttribute('name', `commune${i}`);
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

                document.body.style.cursor = 'default';
            });
        });
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
            if (formDestination.children[i].children[1].value !== '')
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

    target.children[1].value = data.nom_comm;
    target.children[3].value = data.gid;
    verifyFilledField();
}
