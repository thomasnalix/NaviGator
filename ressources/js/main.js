const calculButton = document.getElementById('calcul');
const addDestination = document.getElementById('addDestination');
const formDestination = document.getElementById('formDestination');
const close = document.getElementsByClassName('close');
const nbField = document.getElementById('nbField');

// Création d'un field
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
        input.addEventListener('input', e => autocomplete(dataList, e.target.value));

        div.appendChild(input);
        div.appendChild(dataList);

        const gidInput = document.createElement('input');
        gidInput.type = 'hidden';
        gidInput.name = `gid${nbChild}`;
        gidInput.id = `gid${nbChild}`;
        div.appendChild(gidInput);

        const iconRight = document.createElement('span');
        iconRight.classList.add('material-symbols-outlined', 'close');
        iconRight.textContent = 'close';
        div.appendChild(iconRight);

        formDestination.appendChild(div);
        nbField.setAttribute('value', nbChild + 1);
        verifyChild();
        verifyFilledField();
        init();
    } else {
        addDestination.style.display = 'none';
    }
});

//when click on close class icon, remove the parent field
function init() {
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


function verifyChild() {
    const nbChild = formDestination.childElementCount;
    const display = nbChild > 2 ? '' : 'none';
    for (let i = 0; i < close.length; i++)
        close[i].style.display = display;
}

// If all field input of the fox formDestination are filled, addDestination is affiched
formDestination.addEventListener('input', function () {
    verifyFilledField();
});

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

map.on('click', function (e) {
    // get the coordinates of the click
    let coord = ol.proj.transform(e.coordinate, 'EPSG:3857', 'EPSG:4326');
    let lon = coord[0];
    let lat = coord[1];

    send(lon, lat);
    //alert("Vous avez cliqué sur la longitude : " + lon + " et la latitude : " + lat);
});

async function send(long, lat) {
    const url = 'controleurFrontal.php?controleur=noeudCommune&action=getNoeudProche&long=' + long + '&lat=' + lat;
    const response = await fetch(url);
    const data = await response.json();
    // set value with data response of the first field empty of formDestination
    for (let i = 0; i < formDestination.childElementCount; i++) {
        if (formDestination.children[i].children[1].value === '') {
            formDestination.children[i].children[1].value = data.route;
            formDestination.children[i].children[2].value = data.gid;
            break;
        }
    }
    verifyFilledField();

}
