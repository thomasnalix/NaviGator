/**
 * add point on map according to lon and lat and remove old point if exist
 * @param target
 * @param lon
 * @param lat
 * @param nomCommune
 */
function addPointOnMap(target, lon, lat, nomCommune = "") {
    let layer = map.getLayers().getArray();
    for (let i = 0; i < layer.length; i++) {
        if (layer[i].get('name') === target) {
            map.removeLayer(layer[i]);
        }
    }

    let point = new ol.geom.Point(ol.proj.fromLonLat([lon, lat]));
    let feature = new ol.Feature({
        geometry: point,
        name: target
    });
    let vectorSource = new ol.source.Vector({
        features: [feature]
    });
    let text = new ol.style.Text({
        text: nomCommune,
        offsetY: 5,
        stroke: new ol.style.Stroke({
            color: '#fff',
            width: 1
        }),
        font: 'bold 10px Poppins',
        fill: new ol.style.Fill({
            color: '#000'
        })
    });
    let vectorLayer = new ol.layer.Vector({
        source: vectorSource,
        name: target,
        style: new ol.style.Style({
            image: new ol.style.Icon({
                anchor: [0.5, 1],
                src: '../ressources/img/map_point.png',
                // set the size of the icon
                scale: 0.1
            }),
            text: text
        })
    });
    map.addLayer(vectorLayer);

}

/**
 * Remove point on map according to name
 * @param name
 */
function removePointOnMap(name) {
    let layer = map.getLayers().getArray();
    for (let i = 0; i < layer.length; i++) {
        if (layer[i].get('name') === name) {
            map.removeLayer(layer[i]);
        }
    }
}

/**
 * Print on the map the itinary
 * @param path
 */
function printItinary(path) {
    // Supprimer la couche vectorielle si elle existe
    let layer = map.getLayers().getArray();
    for (let i = 0; i < layer.length; i++) {
        if (layer[i].get('name') === 'itinary') {
            map.removeLayer(layer[i]);
        }
    }

    let geometries = [];
    if (path.length === 0) {
        return;
    }
    path.forEach(function (coord) {
        geometries.push(new ol.format.WKB().readGeometry(coord, {
            dataProjection: 'EPSG:4326',  // Projection de la coordonnée d'entrée
            featureProjection: 'EPSG:3857'  // Projection de la carte (Web Mercator)
        }));
    })

    let lineStyle = new ol.style.Style({
        // crate line with 6ce3a3 and 7px width AND 2px width with 000000
        stroke: new ol.style.Stroke({
            color: '#0ac876',
            width: 7,
        })
    });

    // Créer une couche vectorielle à partir du tableau de géométries
    let vectorLayer = new ol.layer.Vector({
        source: new ol.source.Vector({
            features: geometries.map(geometry => new ol.Feature({geometry})),
        }),
        style: lineStyle,
        name: 'itinary',
    });


    // add to the map the vector layer in background position
    map.getLayers().insertAt(1, vectorLayer);

    // put layer in last position
    setTimeout(zoomToLine, 1);

    function zoomToLine() {
        let view = map.getView();
        let source = map.getLayers().getArray()[1].getSource();
        let extent = source.getExtent();
        view.fit(extent, {maxZoom: 20, duration: 2000, padding: [150, 150, 150, 150]});
    }


}