// ============ TEMA OSCURO/CLARO ============
function activarTemaHeader() {
    const cuerpo = document.body;
    // Usamos querySelector porque los IDs deben ser únicos, pero si hay múltiples por alguna razón, tomamos el primero o iteramos.
    // En este caso, mantenemos la lógica de soportar múltiples botones por si acaso.
    const botones = document.querySelectorAll('[id="modoLecturaBtn"]');

    const temaSaved = localStorage.getItem('tema');
    const esOscuro = temaSaved === 'oscuro';

    // Aplicar clases al body
    if (esOscuro) {
        cuerpo.classList.add('modo-oscuro');
        cuerpo.classList.remove('modo-claro');
    } else {
        cuerpo.classList.add('modo-claro');
        cuerpo.classList.remove('modo-oscuro');
    }

    if (!botones.length) return;

    botones.forEach(boton => {
        // Clonamos el botón para eliminar event listeners previos si la función se ejecuta varias veces
        // O mejor, verificamos si ya tiene el listener para no duplicar, pero clonar es una forma drástica y efectiva de limpiar.
        // Sin embargo, para ser menos destructivos, simplemente reasignamos el onclick (que sobrescribe el anterior).

        const moonIcon = boton.querySelector('[id="moonIcon"]');
        const sunIcon = boton.querySelector('[id="sunIcon"]');

        if (!moonIcon || !sunIcon) return;

        // Actualizar iconos visualmente
        moonIcon.style.display = esOscuro ? 'none' : 'block';
        sunIcon.style.display = esOscuro ? 'block' : 'none';

        // Asignar evento click
        boton.onclick = (e) => {
            e.preventDefault();

            const isOscuroActual = cuerpo.classList.contains('modo-oscuro');
            const nuevoOscuro = !isOscuroActual;

            // Función que realiza el cambio de clases y localStorage
            const updateTheme = () => {
                cuerpo.classList.toggle('modo-oscuro', nuevoOscuro);
                cuerpo.classList.toggle('modo-claro', !nuevoOscuro);
                localStorage.setItem('tema', nuevoOscuro ? 'oscuro' : 'claro');

                // Actualizar iconos
                const todosBotones = document.querySelectorAll('[id="modoLecturaBtn"]');
                todosBotones.forEach(b => {
                    const m = b.querySelector('[id="moonIcon"]');
                    const s = b.querySelector('[id="sunIcon"]');
                    if (m && s) {
                        m.style.display = nuevoOscuro ? 'none' : 'block';
                        s.style.display = nuevoOscuro ? 'block' : 'none';
                    }
                });
            };

            // Usar View Transitions API si está soportada
            if (document.startViewTransition) {
                document.startViewTransition(() => updateTheme());
            } else {
                // Fallback para navegadores antiguos
                updateTheme();
            }
        };
    });
}

// Ejecutar inmediatamente si el DOM ya está listo (para scripts tipo module o defer)
if (document.readyState === 'interactive' || document.readyState === 'complete') {
    activarTemaHeader();
} else {
    document.addEventListener('DOMContentLoaded', activarTemaHeader);
}

// Soporte para Astro View Transitions (si se usan en el futuro)
document.addEventListener('astro:page-load', activarTemaHeader);

// Soporte para BFCache (cuando das "Atrás" en el navegador)
window.addEventListener('pageshow', (event) => {
    if (event.persisted) {
        activarTemaHeader();
    }
});