
const addDestination = document.getElementById('addDestination');
const formDestination = document.getElementById('formDestination');
const close = document.getElementsByClassName('close');
const nbField = document.getElementById('nbField');

// Création d'un field
addDestination.addEventListener('click', function () {
    let nbChild = formDestination.childElementCount;
    if (nbChild < 6) {
        let div = document.createElement('div');
        let iconLeft = document.createElement('span');
        let input = document.createElement('input');
        let iconRight = document.createElement('span');

        div.setAttribute('class', 'input-box');
        iconLeft.setAttribute('class', 'material-symbols-outlined');
        iconLeft.innerHTML = 'flag';
        iconRight.setAttribute('class', 'material-symbols-outlined close');
        iconRight.innerHTML = 'close';
        input.setAttribute('type', 'text');
        input.setAttribute('placeholder', 'Commune de transition');
        input.setAttribute('name', `commune${nbChild}`);
        input.setAttribute('id', `commune${nbChild}`);
        input.setAttribute('required', '');
        div.appendChild(iconLeft);
        div.appendChild(input);
        div.appendChild(iconRight);
        formDestination.appendChild(div);
        nbField.setAttribute('value', nbChild + 1);
        verifyChild();
        init();
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
        }
    }
}




function verifyChild() {
    let nbChild = formDestination.childElementCount;
    if (nbChild > 2) {
        for (let i = 0; i < close.length; i++) {
            close[i].style.display = '';
        }
    } else {
        for (let i = 0; i < close.length; i++) {
            close[i].style.display = 'none';
        }
    }
}