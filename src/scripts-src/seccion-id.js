function mostrarSeccion(valor) {
    const esRuta = typeof valor === 'string' && valor.startsWith('/');
    if (esRuta) {
        window.location.href = valor;
        return;
    }
    const id = valor;
    const enMain = window.location.pathname.toLowerCase().includes('/mainpage');
    if (enMain) {
        const el = document.getElementById(id);
        if (el) {
            el.scrollIntoView({ behavior: 'smooth', block: 'start' });
            history.replaceState({}, '', `#${id}`);
        } else {
            window.location.href = `/mainPage#${id}`;
        }
    } else {
        window.location.href = `/mainPage#${id}`;
    }
}

let esRecargaPagina = performance.navigation.type === 1;

document.addEventListener('astro:page-load', () => {
    if (!esRecargaPagina) {
        const hash = window.location.hash.replace('#', '');
        if (hash) {
            const el = document.getElementById(hash);
            if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }
    esRecargaPagina = false;
});

export default mostrarSeccion;