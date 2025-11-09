// ============ TEMA OSCURO/CLARO ============
function activarTemaHeader() {
    const boton = document.getElementById('modoLecturaBtn');
    const cuerpo = document.body;
    const moonIcon = document.getElementById('moonIcon');
    const sunIcon = document.getElementById('sunIcon');

    if (!boton || !moonIcon || !sunIcon) return;

    // Obtener tema guardado
    const temaSaved = localStorage.getItem('tema');
    if (temaSaved === 'oscuro') {
        cuerpo.classList.add('modo-oscuro');
        cuerpo.classList.remove('modo-claro');
        moonIcon.style.display = 'none';
        sunIcon.style.display = 'block';
    } else {
        cuerpo.classList.remove('modo-oscuro');
        cuerpo.classList.add('modo-claro');
        moonIcon.style.display = 'block';
        sunIcon.style.display = 'none';
    }

    // Click para cambiar tema
    boton.onclick = () => {
        const isOscuro = cuerpo.classList.contains('modo-oscuro');
        
        if (isOscuro) {
            // Cambiar a claro
            cuerpo.classList.remove('modo-oscuro');
            cuerpo.classList.add('modo-claro');
            moonIcon.style.display = 'block';
            sunIcon.style.display = 'none';
            localStorage.setItem('tema', 'claro');
        } else {
            // Cambiar a oscuro
            cuerpo.classList.add('modo-oscuro');
            cuerpo.classList.remove('modo-claro');
            moonIcon.style.display = 'none';
            sunIcon.style.display = 'block';
            localStorage.setItem('tema', 'oscuro');
        }
    };
}

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', activarTemaHeader);

// También ejecutar inmediatamente (para Astro)
activarTemaHeader();