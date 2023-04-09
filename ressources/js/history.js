const history = document.getElementById('history');
let cropTextElements = document.getElementsByClassName('crop-text');
let animationInterval;
const historyChildren = history.children;

for (let child of historyChildren) {
    // add an event listener to each child
    child.addEventListener('click', async () => {
        const id = child.id;
        let url = './getTrajet/' + id;
        const data = await fetch(url);
        const response = await data.json();
        localStorage.setItem('trajet', response);
        window.location.replace('./map');


    });
}

for (let i = 0; i < cropTextElements.length; i++) {
    let cropTextElement = cropTextElements[i];
    if (cropTextElement.scrollWidth > cropTextElement.clientWidth) {
        cropTextElement.classList.add('overflowed-text');
    }
}

function startMarqueeAnimation(e) {
    let initialPosition = 0; // Position de départ
    animationInterval = setInterval(() => {
        initialPosition -= 0.1; // Déplacer la position de -1 à chaque itération
        e.style.transform = `translateX(${initialPosition}%)`; // Appliquer la transformation à l'élément
        if (initialPosition === -100) { // Arrêter l'animation lorsque la position atteint -100
            initialPosition = 0;
        }
        // stop the animation if the end of the text < the parent container right border
        if (e.scrollWidth + e.getBoundingClientRect().left < e.parentElement.getBoundingClientRect().right - 40) {
            initialPosition = 10;
            e.style.transform = 'translateX(10)';
        }
    }, 15); // Intervalle de temps en millisecondes entre chaque itération de l'animation
}

function stopMarqueeAnimation(e) {
    clearInterval(animationInterval); // Effacer l'intervalle d'animation
    e.style.transform = 'translateX(0)'; // Réinitialiser la position de l'élément
}

for (let i = 0; i < cropTextElements.length; i++) {
    let cropTextElement = cropTextElements[i];
    if (cropTextElement.scrollWidth > cropTextElement.clientWidth) {
        cropTextElement.classList.add('overflowed-text');
        cropTextElement.addEventListener('mouseenter', e => startMarqueeAnimation(e.target));
        cropTextElement.addEventListener('mouseleave', e => stopMarqueeAnimation(e.target));
    }
}

