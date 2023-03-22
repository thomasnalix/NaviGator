<script defer src="../ressources/js/main.js" xmlns="http://www.w3.org/1999/html" xmlns="http://www.w3.org/1999/html"></script>
<script defer src="../ressources/js/autocompletion.js" xmlns="http://www.w3.org/1999/html"></script>
<div class="itinerary-box">
    <form action="" method="post" autocomplete="off">
        <div id="formDestination" class="flex flex-col gap-4">
            <div class="input-box">
                <span class="material-symbols-outlined">pin_drop</span>
                <input type="text" list="auto-completion-0" value="" placeholder="Commune de départ" name="commune0" class="commune" id="commune0" required>
                <datalist id="auto-completion-0"></datalist>
                <span class="material-symbols-outlined close" style="display: none;">close</span>
            </div>
            <div class="input-box">
                <span class="material-symbols-outlined">flag</span>
                <input type="text" list="auto-completion-1" value="" placeholder="Commune d'arrivée" name="commune1" class="commune" id="commune1" required>
                <datalist id="auto-completion-1"></datalist>
                <span class="material-symbols-outlined close" style="display: none;">close</span>
            </div>
        </div>
        <div id="addDestination" class="box-flex">
            <span class="material-symbols-outlined">add_circle</span>
            <p>Ajouter une destination</p>
        </div>
        <input type="hidden" name="nbField" id="nbField" value="2">
        <input type="hidden" name="XDEBUG_TRIGGER">
        <input class="button-box"  id="calcul" type="submit" disabled value="Calculer"/>
    </form>
    <?php if (!empty($_POST)) {
        echo '<p>
                ' . $nomCommuneDepart . ' vers ' . $nomCommuneArrivee . ' : ' . $distance . 'km. temps : ' . gmdate('H:i:s', floor($temps * 3600)) . ' 
              </p>';
    }
    ?>
</div>

<div id="map"></div>
<script defer>

    let center = ol.proj.fromLonLat([0.3522, 45.8566]);

    let map = new ol.Map({
        target: 'map',
        layers: [
            new ol.layer.Tile({
                source: new ol.source.OSM()
            })
        ],
        view: new ol.View({
            center: center,
            zoom: 6,
            zoomControl: false
        })
    });

    // if the php variable is not empty, we display the map
    let wkb = <?php echo json_encode($chemin ?? []) ?>;
    if (wkb.length > 0) {
        let geometries = [];
        // the wkb array must be foreached and converted to geojson
        wkb.forEach(function(coord) {
            geometries.push(new ol.format.WKB().readGeometry(coord, {
                dataProjection: 'EPSG:4326',  // Projection de la coordonnée d'entrée
                featureProjection: 'EPSG:3857'  // Projection de la carte (Web Mercator)
            }));
        })

        // Définir un style de ligne rouge avec une épaisseur de 4 pixels
        let lineStyle = new ol.style.Style({
            stroke: new ol.style.Stroke({
                color: '#0c7847',
                width: 5
            })
        });

        // Créer une couche vectorielle à partir du tableau de géométries
        let vectorLayer = new ol.layer.Vector({
            source: new ol.source.Vector({
                features: geometries.map(geometry => new ol.Feature({geometry}))
            }),
            style: lineStyle
        });

        // add to the map the vector layer
        map.addLayer(vectorLayer);
    }

    function zoomToLine() {
        let view = map.getView();
        let source = map.getLayers().getArray()[1].getSource();
        let extent = source.getExtent();
        view.fit(extent, {maxZoom: 20, duration: 2000, padding: [150, 150, 150, 150]});
    }

    window.onload = zoomToLine;


</script>
