const BASE_URL = "https://api.api-ninjas.com/v1/cars?";
const API_KEY = "z0FzwZI3t8fLYYgPD59iCw==oRy5p1Am3hLavzvv"; // https://api-ninjas.com/api/cars

const voiture = document.getElementById("voiture");
const message = document.getElementById("message");
const loading = document.getElementById("loading");

voiture.addEventListener("submit", async e => {
    e.preventDefault();
    loading.classList.remove("hidden");
    await fetch('./voiture', {
        method: 'POST',
        body: new FormData(voiture)
    });
    const data = new FormData(voiture);
    const car = await getFirstCar({
        make: data.get("marque"),
        model: data.get("modele")
    });
    loading.classList.add("hidden");
    if (car === undefined)
        message.innerText = "Voiture introuvable";
    else
        message.innerText = `Voiture trouvé, votre consommation sera de ${getFuelConsumption(car, 100)}/100km`;
});

async function getCar(params) {
    const url = BASE_URL + new URLSearchParams(params);
    return fetch(url, {
        method: "GET",
        headers: {
            "X-Api-Key": API_KEY
        }
    })
        .then(response => response.json())
}

async function getFirstCar(params) {
    return getCar(params).then(data => data[0])
}

function getFuelConsumption(data, distance) {
    if (data === undefined)
        return (-6.3 * distance / 100).toFixed(2)
    if (data.fuel_type === "electricity")
        return `${(data.highway_mpg * 0.425143707 * 0.4 * (distance / 100)).toFixed(2)} kWh`
    else if (data.fuel_type === "gas")
        return `${(235.214583 / data.highway_mpg * (distance / 100)).toFixed(2)} L`
    else if (data.fuel_type === "diesel")
        return `${(235.214583 / data.highway_mpg * 1.1 * (distance / 100)).toFixed(2)} L`
    else
        return (-6.3 * distance / 100).toFixed(2)
}