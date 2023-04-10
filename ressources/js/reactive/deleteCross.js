import {reactive} from "./reactive.js";
import {buttonLocation} from "./targetLocation.js";

export {cross};

let cross = reactive({
}, "crossX");

cross.click = function (i) {
    i = parseInt(i);

    let cross = document.querySelectorAll(`[data-id="${i}"]`)[0];

    let nbChild = formDestination.childElementCount;
    if (nbChild > 2) {
        //Je dois refaire ces deux fonctions par contre pcq ça ça marche pas mais c'est la partie graphique
        removePointOnMap(cross.parentElement.children[0].name);
        updateWhenDelete(cross.parentElement.children[0].name);
        cross.parentElement.remove();

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
    updateIdInput();
    verifyChild();
    changeAddStepButton();

    buttonLocation.buttonNumber--;
}