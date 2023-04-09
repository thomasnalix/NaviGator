import {applyAndRegister, reactive, startReactiveDom} from "./reactive.js";
import {buttonLocation} from "./targetLocation.js";

export {cross};

let cross = reactive({
    crossNumber: 1,
    crosses: document.getElementsByClassName('close')
}, "crossX");

cross.click = function (i) {
    i = parseInt(i);
    if (i === 10) i = (cross.crosses.length - 1);
    else i = i - 1;

    let nbChild = formDestination.childElementCount;
    if (nbChild > 2) {
        removePointOnMap(cross.crosses[i].parentElement.children[0].name);
        updateWhenDelete(cross.crosses[i].parentElement.children[0].name);
        console.log(cross.crosses[i]);
        cross.crosses[i].parentElement.remove();

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
    cross.crossNumber--;
}