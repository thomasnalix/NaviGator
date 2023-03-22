const destionationInputs = document.getElementsByClassName('commune');

function debounce(func, wait) {
    let timeout;
    return function (...args) {
        const context = this;
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(context, args), wait);
    }
}

for (let destionationInput of destionationInputs)
    destionationInput.addEventListener('input', e => autocomplete(destionationInput.nextSibling.nextSibling, e.target.value));

function autocomplete(citiesList, text) {
    // should send a request to the server to get the list of cities and display them in the datalist
    if (text.length < 3) return;
    const url = 'controleurFrontal.php?action=recupererListeCommunes&controleur=noeudCommune&text=' + text;
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