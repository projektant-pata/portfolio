let elo = document.getElementById('elo');
let repos = document.getElementById('github-repos');
if (elo) {
    fetch('https://api.chess.com/pub/player/ObviousCommander/stats')
        .then(response => {
            if (!response.ok) {
                throw new Error('Chyba při načítání dat: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            elo.innerHTML = data['chess_rapid']['best']['rating'];
        })
        .catch(error => {
            console.error('Chyba:', error);
        });
}

if (repos){
    fetch('https://api.github.com/users/projektant-pata')
        .then(response =>{
            if (!response.ok) {
                throw new Error('Chyba při načítání dat: ' + response.status);
            }
            return response.json();
        })
        .then(data =>{
            repos.innerHTML = data['public_repos'];
        })
}
