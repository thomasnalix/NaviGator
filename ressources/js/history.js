const history = document.getElementById('history');


const historyChildren = history.children;
for (let child of historyChildren) {
    // add an event listener to each child
    child.addEventListener('click', async e => {
        const id = e.target.id;
        // place a point on the map
        let url = './getTrajet/' + id;
        const data = await fetch(url);
        const trajet = await data.json();

    });
}