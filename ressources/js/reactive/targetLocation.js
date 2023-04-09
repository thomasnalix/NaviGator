import {applyAndRegister, reactive, startReactiveDom} from "./reactive.js";
import {cross} from "./deleteCross.js";
export {buttonLocation};

let buttonLocation = reactive({
    buttonNumber: 1,
    buttons: document.getElementById('formDestination').children
}, "buttonLoc");

buttonLocation.click = function (i) {
    i = parseInt(i)
    if (i === 10) i = buttonLocation.buttons.length
    document.body.style.cursor = 'crosshair';

    let find = buttonLocation.buttons[i - 1];
    // console.log(buttonLocation.buttons)
    // console.log("Bouton " + i + " = ")
    // console.log(find)
    // console.log("---------------------")

    // use loc the find variable on click
    map.once('click', function (evt) {
        if (find === undefined) return;
        let coord = ol.proj.transform(evt.coordinate, 'EPSG:3857', 'EPSG:4326');
        let lon = coord[0];
        let lat = coord[1];
        let target = find;
        getNearestNode(lon, lat, target);

        // if there is already a point according to e.target.parentElement.children[0].value, remove it and add new point
        addPointOnMap(target.children[0].name, lon, lat);
        document.body.style.cursor = 'default';
    });

    console.log("ButtonNumber : " + buttonLocation.buttonNumber)
    console.log("CrossNumber : " + cross.crossNumber)
    console.log("---------------------")
}

buttonLocation.refresh = function() {
    buttonLocation.buttons = document.getElementById('formDestination').children;
    // startReactiveDom();
}

buttonLocation.backFields = function() {
    let btn = [];
    for (let i = 1; i < buttonLocation.buttonNumber; i++) {
        btn.push(`
            <div class="input-box">
                <input type="text"
                       list="auto-completion-${i-1}"
                       placeholder="Commune de départ"
                       name="commune${i-1}"
                       class="commune"
                       id="commune${i-1}"
                       required>
                <datalist id="auto-completion-${i-1}"></datalist>
                <input type="hidden" name="gid${i-1}" id="gid${i-1}">
                <span class="locate-button material-symbols-outlined"
                      data-onclick="buttonLoc.click(${i})">my_location</span>
                <span class="material-symbols-outlined close"
                      data-onclick="crossX.click(${i})">close</span>
            </div>    
        `)
    }
    return btn.join("");
}

applyAndRegister(() => buttonLocation.buttonNumber);

startReactiveDom();