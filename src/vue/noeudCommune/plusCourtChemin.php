<script defer src="../ressources/js/main.js"></script>
<script defer src="../ressources/js/autocompletion.js"></script>
<script defer src="../ressources/js/mapInteraction.js"></script>
<div class="flex flex-col absolute z-100">
    <div class="itinerary-box box-blur">
        <form id="form">
            <div class="flex mb-6">
                <div class="flex-col items-center flex space-between p-4" id="flag-box">
                    <span class="material-symbols-outlined">pin_drop</span>
                    <span class="material-symbols-outlined">flag</span>
                </div>
                <div class="flex-col flex gap-4 w-full">
                    <div id="formDestination" class="flex flex-col gap-4 w-full">
                        <div class="input-box">
                            <input type="text" list="auto-completion-0" value="" placeholder="Commune de départ" name="commune0" class="commune" id="commune0" required>
                            <datalist id="auto-completion-0"></datalist>
                            <input type="hidden" name="gid0" id="gid0">
                            <span class="locate-button material-symbols-outlined">my_location</span>
                            <span class="material-symbols-outlined close">close</span>
                        </div>
                        <div class="input-box">
                            <input type="text" list="auto-completion-1" value="" placeholder="Commune d'arrivée" name="commune1" class="commune" id="commune1" required>
                            <datalist id="auto-completion-1"></datalist>
                            <input type="hidden" name="gid1" id="gid1">
                            <span class="locate-button material-symbols-outlined">my_location</span>
                            <span class="material-symbols-outlined close">close</span>
                        </div>
                    </div>
                </div>
            </div>
            <div id="addDestination" class="box-flex mb-10">
                <span class="material-symbols-outlined">add_circle</span>
                <p>Ajouter une étape</p>
            </div>
            <input type="hidden" name="nbField" id="nbField" value="2">
            <input type="hidden" name="XDEBUG_TRIGGER">
            <input class="button-box text-center" id="calcul" value="Calculer" type="submit" disabled/>
        </form>
    </div>
    <div class="box-blur flex-col flex" id="result">
        <p id="resume-field">Erreur lors du chargement</p>
        <div class="flex gap-4">
            <span class="material-symbols-outlined">schedule</span>
            <p id="time-field">Erreur</p>
        </div>
        <div class="flex gap-4">
            <span class="material-symbols-outlined">directions_car</span>
            <p id="distance-field">Erreur</p>
        </div>
        <div class="flex gap-4">
            <span class="material-symbols-outlined">local_gas_station</span>
            <p id="gas-field">Erreur</p>
        </div>
    </div>
</div>
<div id="map"></div>
<div id="popup" class="absolute"></div>
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
</script>
