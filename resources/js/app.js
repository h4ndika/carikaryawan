import './bootstrap';

// Global fallback handler for broken images
window.addEventListener('error', function (e) {
    if (e.target && e.target.tagName === 'IMG') {
        const img = e.target;
        // If image is inside a gallery, prose figure, or marked with data-hide-on-error, remove container
        if (img.dataset.hideOnError || img.closest('.gallery-item') || img.closest('[data-hide-on-error]')) {
            const container = img.closest('.gallery-item') || img.closest('[data-hide-on-error]') || img.closest('figure');
            if (container) {
                const parent = container.parentElement;
                container.remove();
                if (parent && parent.querySelectorAll('figure, .gallery-item').length === 0) {
                    parent.closest('section')?.remove();
                }
                return;
            }
        }
        if (!img.dataset.fallbackApplied) {
            img.dataset.fallbackApplied = 'true';
            img.src = '/images/placeholder.svg';
        }
    }
}, true);
