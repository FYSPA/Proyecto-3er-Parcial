
function activarTemaHeader() {
    const boton = document.getElementById('modoLecturaBtn');
    const cuerpo = document.body;
    const moonIcon = document.getElementById('moonIcon');
    const sunIcon = document.getElementById('sunIcon');

    if (!boton || !moonIcon || !sunIcon) return;

    // SIEMPRE leer el estado guardado al cargar
    const temaSaved = localStorage.getItem('tema');
    if (temaSaved === 'oscuro') {
        cuerpo.classList.add('modo-oscuro');
        moonIcon.style.display = 'none';
        sunIcon.style.display = 'block';
    } else {
        cuerpo.classList.remove('modo-oscuro');
        moonIcon.style.display = 'block';
        sunIcon.style.display = 'none';
    }

    // No dupliques listeners
    boton.onclick = () => {
        cuerpo.classList.toggle('modo-oscuro');
        if (cuerpo.classList.contains('modo-oscuro')) {
            moonIcon.style.display = 'none';
            sunIcon.style.display = 'block';
            localStorage.setItem('tema', 'oscuro');
        } else {
            moonIcon.style.display = 'block';
            sunIcon.style.display = 'none';
            localStorage.removeItem('tema');
        }
    };
}

// Para Astro navegación SPA:
document.addEventListener('DOMContentLoaded', activarTemaHeader);
document.addEventListener('astro:after-swap', activarTemaHeader);

