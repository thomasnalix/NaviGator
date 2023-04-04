const BASE_URL = "https://api.api-ninjas.com/v1/cars?";
const API_KEY = "z0FzwZI3t8fLYYgPD59iCw==oRy5p1Am3hLavzvv"; // https://api-ninjas.com/api/cars

async function getCar(params) {
    const url = BASE_URL + new URLSearchParams(params);
    console.log(url)
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

// getFirstCar({make: "Toyota", model: "Corolla", year: "2019"})
//     .then(data => {
//         const mpg = getFuelConsumption(data, 100)
//         console.log(data.model + ": " + mpg);
//     })
//
// getFirstCar({make: "Tesla", model: "Model 3", year: "2019"})
//     .then(data => {
//         const mpg = getFuelConsumption(data, 100)
//         console.log(data.model + ": " + mpg);
//     })

function getFuelConsumption(data, x) {
    console.log(data.transmission)
    if (data.fuel_type === "electricity") {
        // return consumtion in kWh for x km
        return `${data.highway_mpg / 33.705 * x} kWh`
    } else if (data.fuel_type === "gas") {
        return `${235.214583 / data.highway_mpg * (x / 100)} L`
    } else if (data.fuel_type === "diesel") {
        return `${235.214583 / data.highway_mpg * 1.1 * (x / 100)} L`
    } else {
        return "-1";
    }
}