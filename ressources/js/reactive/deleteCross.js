import {applyAndRegister, reactive, startReactiveDom} from "./reactive";
import {buttonLocation} from "./targetLocation.js";

export {cross};

let cross = reactive({
    crossNumber: 2,
    crosses: document.getElementsByClassName('close')
}, "cross");

cross.click = function (i) {
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
    buttonLocation.buttonNumber--;
    cross.crossNumber--;
}

applyAndRegister(() => cross.crossNumber);

startReactiveDom();

