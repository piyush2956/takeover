// Initialize Google Translate Element
function googleTranslateElementInit() {
    new google.translate.TranslateElement({
        pageLanguage: 'en',
        includedLanguages: 'en,fr,ar',
        autoDisplay: false
    }, 'google_translate_element');
}

// Helper function to trigger Google Translate
function changeLanguage(languageCode) {
    const iframe = document.querySelector('.goog-te-menu-frame');
    if (!iframe) return;

    const select = iframe.contentDocument.querySelector('.goog-te-menu2');
    if (!select) return;

    const items = select.querySelectorAll('.goog-te-menu2-item');

    items.forEach(item => {
        const text = item.querySelector('.text').textContent;
        if (text === languageCode) {
            item.click();
            updateLanguageSelector(languageCode);
        }
    });
}

// Update the custom selector to match Google Translate
function updateLanguageSelector(languageCode) {
    const selectors = document.querySelectorAll('.language-select');
    selectors.forEach(selector => {
        selector.value = languageCode;
    });
}

// Hide Google Translate's default widget
document.addEventListener('DOMContentLoaded', () => {
    // Create hidden Google Translate Element
    const div = document.createElement('div');
    div.id = 'google_translate_element';
    div.style.display = 'none';
    document.body.appendChild(div);
});