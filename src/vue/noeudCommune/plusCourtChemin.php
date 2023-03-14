<div class="itinerary-box">
    <form action="" method="post">
        <p class="InputAddOn">
            <label class="InputAddOn-item" for="nomCommuneDepart_id">Nom de la commune de départ</label>
            <input class="InputAddOn-field" type="text" value="" placeholder="Ex : Menton" name="nomCommuneDepart" id="nomCommuneDepart_id" required>
        </p>
        <p class="InputAddOn">
            <label class="InputAddOn-item" for="nomCommuneArrivee_id">Nom de la commune de départ</label>
            <input class="InputAddOn-field" type="text" value="" placeholder="Ex : Menton" name="nomCommuneArrivee" id="nomCommuneArrivee_id" required>
        </p>
        <input type="hidden" name="XDEBUG_TRIGGER">
        <p>
            <input class="InputAddOn-field" type="submit" value="Calculer" />
        </p>
    </form>
    <?php if (!empty($_POST)) {
        echo '<p>
                Le plus court chemin entre ' . $nomCommuneDepart . ' et ' . $nomCommuneArrivee . ' mesure ' . $distance . 'km.
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
                color: 'blue',
                width: 4
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

</script>
