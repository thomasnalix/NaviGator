const history = document.getElementById('history');


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