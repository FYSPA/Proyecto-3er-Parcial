// Limpiar Google Translate al inicio - ANTES de cargar el script
function resetGoogleTranslate() {    
    // Limpiar cookies
    document.cookie = 'googtrans=;path=/;max-age=0;';
    document.cookie = 'googtrans=;domain=' + window.location.hostname + ';path=/;max-age=0;';
    
    // Limpiar localStorage
    localStorage.removeItem('googtrans');
    
    // Limpiar hash
    if (window.location.hash.includes('googtrans')) {
        window.location.hash = '';
    }
    
    // Limpiar atributo lang del HTML
    document.documentElement.lang = 'es';
    
}

// Ejecutar ANTES de cargar Google Translate
resetGoogleTranslate();

// Cargar Google Translate
if (!window.__googleTranslateLoaded) {
  const script = document.createElement('script');
  script.src = 'https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit';
  script.async = true;
  document.head.appendChild(script);
  window.__googleTranslateLoaded = true;
}

window.googleTranslateElementInit = function() {
    new google.translate.TranslateElement({
        pageLanguage: 'es',
        includedLanguages: 'es,en,fr,it,de,pt',
        layout: google.translate.TranslateElement.InlineLayout.VERTICAL,
        autoDisplay: false
    }, 'google_translate_element');
};

function changeLanguage(langCode) {
    
    if (langCode === 'es') {
        // Resetear a español
        resetGoogleTranslate();
        setTimeout(() => location.reload(), 100);
    } else {
        // Cambiar a otro idioma
        const combo = document.querySelector('.goog-te-combo');
        if (combo) {
            combo.value = langCode;
            combo.dispatchEvent(new Event('change', { bubbles: true }));
        }
    }
}

function setupMenu() {
  const containers = document.querySelectorAll('.menu-container');
  containers.forEach(container => {
    const btn = container.querySelector('[id="btn-globo"]');
    const menu = container.querySelector('[id="menu-idiomas"]');
    if (!btn || !menu) return;
    btn.onclick = (e) => {
      e.stopPropagation();
      menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
    };
    document.addEventListener('click', (e) => {
      if (menu.style.display === 'block' && !btn.contains(e.target) && !menu.contains(e.target)) {
        menu.style.display = 'none';
      }
    });
    menu.querySelectorAll('.lang-link').forEach(link => {
      link.onclick = (e) => {
        e.preventDefault();
        const lang = link.getAttribute('data-lang');
        changeLanguage(lang);
        menu.style.display = 'none';
      };
    });
  });
}

// Ejecutar
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', setupMenu);
} else {
    setupMenu();
}

// Para Astro - resetear en cada navegación
document.addEventListener('astro:page-load', () => {
  resetGoogleTranslate();
  setTimeout(setupMenu, 200);
});
