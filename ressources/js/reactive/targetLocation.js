import {applyAndRegister, reactive, startReactiveDom} from "./reactive.js";
export {buttonLocation};

let buttonLocation = reactive({
    click: function () {
        document.body.style.cursor = 'crosshair';

        this.addListener();
    },
    addListener: function () {
        let elements = document.querySelectorAll('[data-onclick="buttonLoc.click()"]');

        for (let element of elements) {
            element.addEventListener('click', (event) => {                      // C'est infâme comment ça me semble pas réactif du tout... Mais je vois pas autrement.
                map.once('click', function (evt) {
                    let coord = ol.proj.transform(evt.coordinate, 'EPSG:3857', 'EPSG:4326');
                    let lon = coord[0];
                    let lat = coord[1];
                    let target = event.target.parentElement;
                    getNearestNode(lon, lat, target);

                    // if there is already a point according to e.target.parentElement.children[0].value, remove it and add new point
                    addPointOnMap(target.children[0].name, lon, lat);
                    document.body.style.cursor = 'default';
                });
            });
        }
    }
}, "buttonLoc");

applyAndRegister(() => buttonLocation.click());

startReactiveDom();