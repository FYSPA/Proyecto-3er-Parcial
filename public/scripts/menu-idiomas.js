const googleTranslateScript = document.createElement('script');
googleTranslateScript.type = 'text/javascript';
googleTranslateScript.src = 'https://translate.google.com/translate_a/element.js';
document.body.appendChild(googleTranslateScript);



function waitForGoogleToLoad(callback) {
    let attempts = 0;
    const intervalId = setInterval(function() {
        // ¡NUEVA VERIFICACIÓN!
        // Esperamos a que la función constructora exista
        if (
            typeof google === 'object' &&
            typeof google.translate === 'object' &&
            typeof google.translate.TranslateElement === 'function' // <--- ESTA ES LA CLAVE
        ) {
            clearInterval(intervalId);
            callback(); // ¡Listo!
        } else {
            attempts++;
            if (attempts > 20) { // Esperar 6 segundos
                clearInterval(intervalId);
            }
        }
    }, 300); // Revisa cada 300ms
}

function changeGoogleLanguage(langCode) {
    const maxAttempts = 10;
    let attempts = 0;

    const intervalId = setInterval(function() {
        const googleCombo = document.querySelector('#google_translate_element .goog-te-combo');

        if (googleCombo) {
            clearInterval(intervalId); 
            googleCombo.value = langCode;

            let event;
            if (typeof(Event) === 'function') {
                event = new Event('change');
            } else {
                event = document.createEvent('Event');
                event.initEvent('change', true, true);
            }
            googleCombo.dispatchEvent(event);
            return;
        }

        attempts++;
        if (attempts > maxAttempts) {
            clearInterval(intervalId);
        }
    }, 300);
}

document.addEventListener('DOMContentLoaded', function () {
    const btnGlobo = document.getElementById('btn-globo');
    const menuIdiomas = document.getElementById('menu-idiomas');

    if (!btnGlobo || !menuIdiomas) {
        return;
    }

    // --- ¡INICIALIZACIÓN MANUAL! ---
    waitForGoogleToLoad(function () {
        new google.translate.TranslateElement({
            pageLanguage: 'es',
            includedLanguages: 'es,en,fr,it,de,pt',
            layout: google.translate.TranslateElement.InlineLayout.SIMPLE,
            autoDisplay: false
        }, 'google_translate_element');
    });

    // --- Lógica de tu menú (sin cambios) ---
    
    // ABRIR/CERRAR
    btnGlobo.addEventListener('click', function (e) {
        e.stopPropagation();
        const isOpen = menuIdiomas.style.display === 'block';
        menuIdiomas.style.display = isOpen ? 'none' : 'block';
    });

    // CERRAR AL HACER CLIC FUERA
    document.addEventListener('click', function (e) {
        if (
            menuIdiomas.style.display === 'block' &&
            !btnGlobo.contains(e.target) &&
            !menuIdiomas.contains(e.target)
        ) {
            menuIdiomas.style.display = 'none';
        }
    });

    // TRADUCIR
    menuIdiomas.addEventListener('click', function (e) {
        const link = e.target.closest('.lang-link');
        if (link) {
            e.preventDefault(); 
            const langCode = link.getAttribute('data-lang');
            changeGoogleLanguage(langCode);
            menuIdiomas.style.display = 'none';
        }
    });
});