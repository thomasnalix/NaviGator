const calculButton = document.getElementById('calcul');
const addDestination = document.getElementById('addDestination');
const formDestination = document.getElementById('formDestination');
const close = document.getElementsByClassName('close');
const nbField = document.getElementById('nbField');

// Création d'un field
addDestination.addEventListener('click', function () {
    let nbChild = formDestination.childElementCount;
    if (nbChild < 6) {
        const div = document.createElement('div');
        div.classList.add('input-box');

        const iconLeft = document.createElement('span');
        iconLeft.classList.add('material-symbols-outlined');
        iconLeft.textContent = 'flag';
        div.appendChild(iconLeft);

        const input = document.createElement('input');
        input.type = 'text';
        input.placeholder = 'Commune de transition';
        input.name = `commune${nbChild}`;
        input.id = `commune${nbChild}`;
        input.required = true;
        div.appendChild(input);

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
    if (nbChild < 6) {
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