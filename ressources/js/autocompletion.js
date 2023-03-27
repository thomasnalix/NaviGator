const destionationInputs = document.getElementsByClassName('commune');

// use debounce function

for (let destionationInput of destionationInputs)
    destionationInput.addEventListener('input', debounce(e => autocomplete(destionationInput.nextSibling.nextSibling, e.target.value), 200));

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