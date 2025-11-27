// src/scripts/theme-toggle.ts

declare global {
    interface Document {
        startViewTransition?: (callback: () => void | Promise<void>) => {
            ready: Promise<void>;
            finished: Promise<void>;
            updateCallbackDone: Promise<void>;
        };
    }
    interface Window {
        SafeStorage: {
            getItem: (key: string) => string | null;
            setItem: (key: string, value: string) => void;
        };
    }
}

function activarTemaHeader(): void {
    const cuerpo = document.body;
    
    const botones = document.querySelectorAll('[id="modoLecturaBtn"]');

    const temaSaved = window.SafeStorage.getItem('tema');
    const esOscuro = temaSaved === 'oscuro';

    const aplicarClases = (oscuro: boolean) => {
        if (oscuro) {
            cuerpo.classList.add('modo-oscuro');
            cuerpo.classList.remove('modo-claro');
        } else {
            cuerpo.classList.add('modo-claro');
            cuerpo.classList.remove('modo-oscuro');
        }
    };

    // Aplicar estado inicial
    aplicarClases(esOscuro);

    if (!botones.length) return;

    botones.forEach((nodoBoton) => {
        const boton = nodoBoton as HTMLElement;

        const moonIcon = boton.querySelector('[id="moonIcon"]') as HTMLElement | null;
        const sunIcon = boton.querySelector('[id="sunIcon"]') as HTMLElement | null;

        if (!moonIcon || !sunIcon) return;

        const actualizarIconos = (oscuro: boolean) => {
            moonIcon.style.display = oscuro ? 'none' : 'block';
            sunIcon.style.display = oscuro ? 'block' : 'none';
        };

        // Estado inicial de iconos
        actualizarIconos(esOscuro);

        boton.onclick = (e: MouseEvent) => {
            e.preventDefault();
            const isOscuroActual = cuerpo.classList.contains('modo-oscuro');
            const nuevoOscuro = !isOscuroActual;

            const updateThemeDOM = () => {
                aplicarClases(nuevoOscuro);
                window.SafeStorage.setItem('tema', nuevoOscuro ? 'oscuro' : 'claro');

                const todosBotones = document.querySelectorAll('[id="modoLecturaBtn"]');
                todosBotones.forEach((b) => {
                    const btn = b as HTMLElement;
                    const m = btn.querySelector('[id="moonIcon"]') as HTMLElement | null;
                    const s = btn.querySelector('[id="sunIcon"]') as HTMLElement | null;
                    
                    if (m && s) {
                        m.style.display = nuevoOscuro ? 'none' : 'block';
                        s.style.display = nuevoOscuro ? 'block' : 'none';
                    }
                });
            };

            if (document.startViewTransition) {
                document.startViewTransition(() => updateThemeDOM());
            } else {
                updateThemeDOM();
            }
        };
    });
}

if (document.readyState === 'interactive' || document.readyState === 'complete') {
    activarTemaHeader();
} else {
    document.addEventListener('DOMContentLoaded', activarTemaHeader);
}

document.addEventListener('astro:page-load', activarTemaHeader);
window.addEventListener('pageshow', (event) => {
    if (event.persisted) {
        activarTemaHeader();
    }
});

export {};