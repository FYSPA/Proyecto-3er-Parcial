/**
 * CardGame Client JavaScript - Código que se ejecuta solo en el navegador
 */

// Función para actualizar los datos de un CardGame
function updateCardGame(cardElement, gameInfo) {
    if (!cardElement || !gameInfo) return;
    
    // Actualizar imagen
    const imgElement = cardElement.querySelector('.game-image');
    if (imgElement && gameInfo.img) {
        imgElement.src = gameInfo.img;
        imgElement.alt = gameInfo.title || 'Imagen del juego';
    }
    
    // Actualizar título
    const titleElement = cardElement.querySelector('.card-game-title');
    if (titleElement && gameInfo.title) {
        titleElement.textContent = gameInfo.title;
    }
    
    // Actualizar descripción
    const descriptionElement = cardElement.querySelector('.card-game-description');
    if (descriptionElement && gameInfo.description) {
        descriptionElement.textContent = gameInfo.description;
    }
}

// Función para inicializar CardGames
function initializeCardGames() {}

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', initializeCardGames);

// Hacer funciones disponibles globalmente
window.cardGameUtils = {
    updateCardGame
};
