const history = document.getElementById('history');


const historyChildren = history.children;
for (let child of historyChildren) {
    // add an event listener to each child
    child.addEventListener('click', async e => {
        const id = e.target.id;
        let url = './map/' + id;
        const data = await fetch(url);
        const response = await data.json();
        let trajet = JSON.parse(response);
        // change the window location to the map page and after redirection loaded,
        window.location.href = './map';
        printItinary(trajet.chemin);




    });
}