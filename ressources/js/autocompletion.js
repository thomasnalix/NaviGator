const destionationInputs = document.getElementsByClassName('commune');

for (let destionationInput of destionationInputs) {
    destionationInput.addEventListener('input', debounce(e => autocomplete(destionationInput.list, e.target.value), 200));
    destionationInput.oninput = e => checkForValidInput(e.target);
}

function autocomplete(citiesList, text) {
    // should send a request to the server to get the list of cities and display them in the datalist
    if (text.length < 3) return;
    const url = './communes/' + text;
    fetch(url)
        .then(response => response.json())
        .then(data => {
            citiesList.innerHTML = '';
            for (let city of data) {
                const option = document.createElement('option');
                option.value = city;
                citiesList.appendChild(option);
            }
        })
        .catch(error => console.log(error));
}

function checkForValidInput(input) {
    const citiesList = input.list;
    const options = citiesList.children;
    for (let option of options) {
        if (option.value === input.value) {
            placePoint(input.value.split(' (')[0], input.id);
            return;
        }
    }
}

function placePoint(commune, id) {
    // should send a request to the server to get the coordinates of the city and place a marker on the map
    const url = './communes/coord/' + commune;
    fetch(url)
        .then(response => response.json())
        .then(data => {
            addPointOnMap(id, data.long, data.lat, commune);
        })
        .catch(error => console.log(error));
}