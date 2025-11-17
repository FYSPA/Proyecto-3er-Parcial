// ============ TEMA OSCURO/CLARO ============
function activarTemaHeader() {
    const cuerpo = document.body;
    const botones = Array.from(document.querySelectorAll('[id="modoLecturaBtn"]'));
    if (!botones.length) return;

    const temaSaved = localStorage.getItem('tema');
    const esOscuro = temaSaved === 'oscuro';
    cuerpo.classList.toggle('modo-oscuro', esOscuro);
    cuerpo.classList.toggle('modo-claro', !esOscuro);

    botones.forEach(boton => {
        const moonIcon = boton.querySelector('[id="moonIcon"]');
        const sunIcon = boton.querySelector('[id="sunIcon"]');
        if (!moonIcon || !sunIcon) return;
        moonIcon.style.display = esOscuro ? 'none' : 'block';
        sunIcon.style.display = esOscuro ? 'block' : 'none';
        boton.onclick = () => {
            const isOscuro = cuerpo.classList.contains('modo-oscuro');
            const nuevoOscuro = !isOscuro;
            cuerpo.classList.toggle('modo-oscuro', nuevoOscuro);
            cuerpo.classList.toggle('modo-claro', !nuevoOscuro);
            localStorage.setItem('tema', nuevoOscuro ? 'oscuro' : 'claro');
            botones.forEach(b => {
                const m = b.querySelector('[id="moonIcon"]');
                const s = b.querySelector('[id="sunIcon"]');
                if (m && s) {
                    m.style.display = nuevoOscuro ? 'none' : 'block';
                    s.style.display = nuevoOscuro ? 'block' : 'none';
                }
            });
        };
    });
}

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', activarTemaHeader);
activarTemaHeader();