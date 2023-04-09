import {applyAndRegister, reactive, startReactiveDom} from "./reactive.js";
import {cross} from "./deleteCross.js";
export {buttonLocation};

let buttonLocation = reactive({
    buttonNumber: 1,
    buttons: document.getElementById('formDestination').children
}, "buttonLoc");

buttonLocation.click = function (i) {
    console.log(i)
    i = parseInt(i);
    console.log(`DOING THE ${i} BUTTON`);
    console.log(buttonLocation.buttons);
    console.log(buttonLocation.buttons[i-1]);
    document.body.style.cursor = 'crosshair';

    let find = buttonLocation.buttons[i-1];

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

buttonLocation.refresh = function () {
    // console.log("APPLYING ON click(" + buttonLocation.buttonNumber + ")");
    // console.log(document.getElementById('formDestination').children[buttonLocation.buttonNumber-1]);
    buttonLocation.buttons = document.getElementById('formDestination').children;
    startReactiveDom(document.getElementById('formDestination').children[buttonLocation.buttonNumber-1]);
}

startReactiveDom(document.getElementById('formDestination'));