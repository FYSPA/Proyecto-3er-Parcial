/**
 * CardGame JavaScript - Funcionalidad para manejar datos de juegos
 * Separado del componente Astro para mejor organización
 */

// Configuración de juegos - Aquí puedes definir tus juegos
export const gameData = [
    {
        img: '/logoVGS.svg',
        title: 'Super Aventura',
        description: 'Una emocionante aventura llena de desafíos y misterios por resolver.',
        link: 'https://deepwiki.com/FYSPA/Proyecto-3er-Parcial/1-project-overview'
    },
    {
        img: 'data:image/jpeg;base64,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', 
        title: 'Carreras Extremas',
        description: 'Compite en las carreras más rápidas y desafiantes del mundo.',
        link: ''

    },
    {
        img: '',
        title: 'Puzzle Mágico',
        description: 'Resuelve rompecabezas mágicos y desbloquea nuevos poderes.',
        link: ''
    },
    {
        img: '',
        title: 'Puzzle Mágico',
        description: 'Resuelve rompecabezas mágicos y desbloquea nuevos poderes.',
        link: ''
    },
    {
        img: '',
        title: 'Puzzle Mágico',
        description: 'Resuelve rompecabezas mágicos y desbloquea nuevos poderes.',
        link: ''
    },
    {
        img: '',
        title: 'Puzzle Mágico',
        description: 'Resuelve rompecabezas mágicos y desbloquea nuevos poderes.',
        link: ''
    }
];

// Función para obtener datos de un juego por índice
export function getGameData(index) {
    return gameData[index] || gameData[0];
}

// Función helper para agregar nuevos juegos dinámicamente
export function addGameToData(img, title, description) {
    gameData.push({
        img: img,
        title: title,
        description: description,
        link: link
    });
    return gameData.length - 1;
}

// Función para actualizar datos de un juego específico
export function updateGameData(index, img, title, description) {
    if (gameData[index]) {
        if (img) gameData[index].img = img;
        if (title) gameData[index].title = title;
        if (description) gameData[index].description = description;
        if (link) gameData[index].link = link;
        return true;
    }
    return false;
}
