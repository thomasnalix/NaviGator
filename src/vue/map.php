<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <title>OpenLayers Example</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/ol@v7.2.2/ol.css" />
    <script src="https://cdn.jsdelivr.net/npm/ol@v7.2.2/dist/ol.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/proj4js/2.7.1/proj4.js"></script>
    <script src="https://unpkg.com/proj4@2.7.4/dist/proj4.js"></script>
</head>
<body>
<div id="map" style="width: 100%; height: 500px;"></div>
<script>
    // Convert PostGIS coordinates to a projection compatible with OpenLayers
    // Coordonnées WKB de votre exemple
    let wkb = '0102000020E6100000030000004169D6AF6D68E83FD1DCCEE26DBB4740939E4149E165E83FF6BCDEBE63BB4740ADE7054E755CE83F86C0D7353ABB4740';

    // Créer un objet de géométrie à partir des coordonnées WKB
    let geometry = new ol.format.WKB().readGeometry(wkb, {
        dataProjection: 'EPSG:4326',  // Projection de la coordonnée d'entrée
        featureProjection: 'EPSG:3857'  // Projection de la carte (Web Mercator)
    });

    // Créer un objet de couche à partir de la géométrie
    let layer = new ol.layer.Vector({
        source: new ol.source.Vector({
            features: [new ol.Feature(geometry)]
        })
    });

    // Afficher la carte avec la couche
    let map = new ol.Map({
        target: 'map.php',
        layers: [
            new ol.layer.Tile({
                source: new ol.source.OSM()
            }),
            layer
        ],
        view: new ol.View({
            center: [0, 0],
            zoom: 2
        })
    });

</script>
</body>
</html>
