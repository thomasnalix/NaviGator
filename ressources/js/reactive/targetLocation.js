import {applyAndRegister, reactive, startReactiveDom} from "./reactive.js";
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
    console.log(buttonLocation.buttons)
    console.log("Bouton " + i + " = ")
    console.log(find)
    console.log("---------------------")

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
}

buttonLocation.refresh = function() {
    console.log("refresh")
    buttonLocation.buttons = document.getElementById('formDestination').children;
}

applyAndRegister(() => buttonLocation.buttonNumber);

startReactiveDom();