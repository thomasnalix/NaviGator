import {applyAndRegister, reactive, startReactiveDom} from "./reactive.js";
import {buttonLocation} from "./targetLocation.js";

let buttonAddDestination = reactive({
    class: document.getElementById('addDestination')
}, "buttonAdd");

buttonAddDestination.add = function () {
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
        iconRight.setAttribute('data-onclick', 'buttonLoc.click(' + (buttonLocation.buttonNumber+1) + ')');
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
        buttonLocation.buttonNumber++;
        buttonLocation.refresh();
    } else {
        buttonAddDestination.class.classList.add('disabled');
    }
}

applyAndRegister(() => buttonAddDestination.add());

startReactiveDom();