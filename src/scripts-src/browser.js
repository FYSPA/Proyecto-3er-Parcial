import { games } from '../pages/dashboardpage/gameData.js';

document.addEventListener('astro:page-load', () => {
    const searchInput = document.getElementById('transcription');
    const searchResults = document.getElementById('search-results');

    if (!searchInput || !searchResults) return;

    searchInput.addEventListener('input', (e) => {
        const query = e.target.value.toLowerCase();
        searchResults.innerHTML = '';

        if (query.length === 0) {
            searchResults.style.display = 'none';
            return;
        }

        const filteredGames = games.filter(game =>
            game.titulo.toLowerCase().includes(query)
        );

        if (filteredGames.length > 0) {
            filteredGames.forEach(game => {
                const item = document.createElement('div');
                item.className = 'search-result-item';

                const img = document.createElement('img');
                img.src = game.iconGame || game.iconSrc;
                img.alt = game.titulo;
                img.className = 'search-result-img';

                const text = document.createElement('span');
                text.textContent = game.titulo;

                item.appendChild(img);
                item.appendChild(text);

                item.addEventListener('click', () => {
                    window.location.href = `/${game.ruta}`;
                });

                searchResults.appendChild(item);
            });
            searchResults.style.display = 'block';
        } else {
            const item = document.createElement('div');
            item.className = 'search-result-item';
            item.textContent = 'No se encontraron juegos';
            searchResults.appendChild(item);
            searchResults.style.display = 'block';
        }
    });

    // Close search results when clicking outside
    document.addEventListener('click', (e) => {
        if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
            searchResults.style.display = 'none';
        }
    });
});
