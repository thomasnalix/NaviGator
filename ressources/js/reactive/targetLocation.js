import {reactive, startReactiveDom} from "./reactive.js";
import {cross} from "./deleteCross.js";
export {buttonLocation};

let buttonLocation = reactive({
    buttonNumber: 1,
}, "buttonLoc");

buttonLocation.click = function (i) {
    i = parseInt(i);

    document.body.style.cursor = 'crosshair';

    let find = document.querySelectorAll(`[data-id="${i}"]`)[0].parentElement;

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

buttonLocation.refresh = function () {
    startReactiveDom(document.getElementById('formDestination').children[buttonLocation.buttonNumber-1]);
}

startReactiveDom(document.getElementById('formDestination'));