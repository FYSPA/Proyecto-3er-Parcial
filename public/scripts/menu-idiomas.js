
// Google Translate
function googleTranslateElementInit() {
    new google.translate.TranslateElement({
        pageLanguage: 'es',
        includedLanguages: 'es,en,fr,it,de,pt',
        layout: google.translate.TranslateElement.InlineLayout.SIMPLE
    }, 'google_translate_element');

    // Después de que Google cree el widget, mueve el select al menú
    setTimeout(function () {
        var combo = document.querySelector('.goog-te-combo');
        var menuIdiomas = document.getElementById('menu-idiomas');
        if (combo && menuIdiomas && !menuIdiomas.contains(combo)) {
            menuIdiomas.appendChild(combo);
            console.log('Select movido al menú');
        }
    }, 500);
}

document.addEventListener('DOMContentLoaded', function () {
    const btnGlobo = document.getElementById('btn-globo');
    const menuIdiomas = document.getElementById('menu-idiomas');

    if (btnGlobo && menuIdiomas) {
        btnGlobo.addEventListener('click', function (e) {
            e.stopPropagation();
            const isOpen = menuIdiomas.style.display === 'block';
            menuIdiomas.style.display = isOpen ? 'none' : 'block';
        });

        document.addEventListener('click', function (e) {
            if (
                menuIdiomas.style.display === 'block' &&
                !btnGlobo.contains(e.target) &&
                !menuIdiomas.contains(e.target)
            ) {
                menuIdiomas.style.display = 'none';
            }
        });
    }
});
