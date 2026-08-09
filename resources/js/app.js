import './bootstrap';

document.addEventListener('alpine:init', () => {
    Alpine.store('sheet', {
        open: null,

        toggle(name) {
            this.open = this.open === name ? null : name;
        },

        isOpen(name) {
            return this.open === name;
        },

        close() {
            this.open = null;
        },
    });
});
