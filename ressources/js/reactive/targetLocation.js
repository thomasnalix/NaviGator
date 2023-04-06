import {applyAndRegister, reactive, startReactiveDom} from "./reactive.js";

let buttonLocation = reactive({
    click: function (elementBeeingClicked) {
        document.body.style.cursor = 'crosshair';

        map.once('click', function (evt) {
            let coord = ol.proj.transform(evt.coordinate, 'EPSG:3857', 'EPSG:4326');
            let lon = coord[0];
            let lat = coord[1];
            let target = elementBeeingClicked.parentElement;
            getNearestNode(lon, lat, target);

            // if there is already a point according to e.target.parentElement.children[0].value, remove it and add new point
            addPointOnMap(target.children[0].name, lon, lat);
            document.body.style.cursor = 'default';
        });
    }
}, "buttonLoc");

applyAndRegister(() => buttonLocation.click());

startReactiveDom();