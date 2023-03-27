const URL = "https://apis.solarialabs.com/shine/v1/vehicle-stats/fuel-usage?make={MODELE}&car-truck={TYPE}&apikey={API_KEY}";
const API_KEY = "xrpBox9bFgAJMM1NSTZIICnIAx1vDQzV"; // https://developers.solarialabs.com/

const request = URL.replace("{MODELE}", "ford")
                   .replace("{TYPE}", "car")
                   .replace("{API_KEY}", API_KEY)
fetch(request)
    .then(response => response.json())
    .then(data => console.log(data));