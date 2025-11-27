declare global {
    interface Window {
        googleTranslateElementInit?: () => void;
        __googleTranslateLoaded?: boolean;
        google?: any;
    }
}

const safeStorage = {
    removeItem: (key: string) => {
        try {
            if (typeof window !== 'undefined' && window.localStorage) {
                window.localStorage.removeItem(key);
            }
        } catch (e) {console.log(e)}
    },
    removeCookie: (name: string) => {
        try {
            document.cookie = `${name}=;path=/;max-age=0;`;
            document.cookie = `${name}=;domain=${window.location.hostname};path=/;max-age=0;`;
        } catch (e) {console.log(e)}
    }
};

function resetGoogleTranslate(): void {
    safeStorage.removeCookie('googtrans');
    safeStorage.removeItem('googtrans');

    if (window.location.hash.includes('googtrans')) {
        history.replaceState(null, '', window.location.pathname + window.location.search);
    }
    document.documentElement.lang = 'es';
}
resetGoogleTranslate();

if (!window.__googleTranslateLoaded) {
    const script = document.createElement('script');
    script.src = 'https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit';
    script.async = true;
    document.head.appendChild(script);
    window.__googleTranslateLoaded = true;
}

window.googleTranslateElementInit = function(): void {
    if (window.google && window.google.translate) {
        new window.google.translate.TranslateElement({
            pageLanguage: 'es',
            includedLanguages: 'es,en,fr,it,de,pt',
            layout: window.google.translate.TranslateElement.InlineLayout.VERTICAL,
            autoDisplay: false
        }, 'google_translate_element');
    }
};

function changeLanguage(langCode: string): void {
    if (langCode === 'es') {
        resetGoogleTranslate();
        setTimeout(() => location.reload(), 100);
    } else {
        // Buscar el select oculto de Google
        const combo = document.querySelector('.goog-te-combo') as HTMLSelectElement;
        if (combo) {
            combo.value = langCode;
            combo.dispatchEvent(new Event('change', { bubbles: true }));
        }
    }
}

function setupMenu(): void {
    const containers = document.querySelectorAll('.menu-container');

    containers.forEach(container => {
        const btn = container.querySelector('[id="btn-globo"]') as HTMLElement;
        const menu = container.querySelector('[id="menu-idiomas"]') as HTMLElement;

        if (!btn || !menu) return;
        
        btn.onclick = (e: MouseEvent) => {
            e.preventDefault();
            e.stopPropagation();
            menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
        };

        const closeMenu = (e: MouseEvent) => {
            const target = e.target as Node;
            if (menu.style.display === 'block' && !btn.contains(target) && !menu.contains(target)) {
                menu.style.display = 'none';
            }
        };
        document.addEventListener('click', closeMenu);

        // Links de idiomas
        const links = menu.querySelectorAll('.lang-link');
        links.forEach(link => {
            const anchor = link as HTMLElement; 
            
            anchor.onclick = (e: MouseEvent) => {
                e.preventDefault();
                const lang = anchor.getAttribute('data-lang');
                if (lang) {
                    changeLanguage(lang);
                    menu.style.display = 'none';
                }
            };
        });
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', setupMenu);
} else {
    setupMenu();
}

document.addEventListener('astro:page-load', () => {
    setTimeout(setupMenu, 200);
});

export {};