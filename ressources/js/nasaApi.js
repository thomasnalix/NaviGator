const api = 'SrNfMdtN0h5AVAMJ67AzngfXFztCr8HW2eFipRVJ';

const background = document.querySelector('.background');


getNasaImage();
async function getNasaImage() {
    const response = await fetch(`https://api.nasa.gov/planetary/apod?api_key=${api}`);
    const data = await response.json();
    background.style.backgroundImage = `url(${data.hdurl})`;
}