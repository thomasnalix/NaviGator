import {reactive} from "./reactive.js";
import {buttonLocation} from "./targetLocation.js";

export {cross};

let cross = reactive({
    crosses: document.getElementsByClassName('close')
}, "crossX");

cross.click = function (i) {
    i = parseInt(i);

    cross.crosses = document.getElementsByClassName('close');
    let crossHTML = document.querySelectorAll(`[data-id="${i}"]`)[1];

    // get the index of the cross in the cross.crosses list
    let index = 0;
    for (let j = 0; j < cross.crosses.length; j++) {
        if (cross.crosses[j] === crossHTML) {
            console.log(cross.crosses[j])
            console.log(crossHTML)
            index = j;
            break;
        }
    }

    let nbChild = formDestination.childElementCount;
    if (nbChild > 2) {
        //Je dois refaire ces deux fonctions par contre pcq ça ça marche pas mais c'est la partie graphique
        console.log("Removing the point on map: " + cross.crosses[index].parentElement.children[0].name);
        console.log(cross.crosses[index].parentElement);
        removePointOnMap(cross.crosses[index].parentElement.children[0].name);
        updateWhenDelete(cross.crosses[index].parentElement.children[0].name);
        cross.crosses[index].parentElement.remove();

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