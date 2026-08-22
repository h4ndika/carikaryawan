import './bootstrap';

// Global fallback handler for broken images
window.addEventListener('error', function (e) {
    if (e.target && e.target.tagName === 'IMG') {
        const img = e.target;
        if (!img.dataset.fallbackApplied) {
            img.dataset.fallbackApplied = 'true';
            img.src = '/images/placeholder.svg';
        }
    }
}, true);
