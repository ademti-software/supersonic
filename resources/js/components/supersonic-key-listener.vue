<template/>

<script>
export default {
    props: {
        isVisible: { type: Boolean, required: true },
    },
    data() {
        return {
        };
    },
    mounted() {
        document.addEventListener(
            'keydown',
            (e) => {
                if (!e.target instanceof HTMLElement && !e.target instanceof document) {
                    return;
                }

                // Open / Close the modal
                if (( e.metaKey || e.ctrlKey ) && e.code === 'KeyK') {
                    this.$emit('toggleVisibility');
                    return e.preventDefault();
                }
                if (e.code === 'Escape') {
                    this.$emit('visibilityHide');
                    return e.preventDefault();
                }

                // If we get here, and the modal isn't active, then we're done, do nothing, and let the press propagate.
                if (!this.isVisible) {
                    return;
                }

                // Navigation within the modal actions.
                if (e.code === 'ArrowDown') {
                    this.$emit('selectNext');
                    return e.preventDefault();
                }
                if (e.code === 'ArrowUp') {
                    this.$emit('selectPrevious');
                    return e.preventDefault();
                }

                // Action selection
                if (!(e.metaKey || e.ctrlKey) && e.code === 'Enter' ) {
                    this.$emit('primaryAction');
                    return e.preventDefault();
                }
                if (( e.metaKey || e.ctrlKey ) && e.code === 'Enter') {
                    this.$emit('secondaryAction');
                    return e.preventDefault();
                }
                if (( e.metaKey || e.ctrlKey ) && e.code === 'ArrowRight') {
                    this.$emit('tertiaryAction');
                    return e.preventDefault();
                }
                if (( e.metaKey || e.ctrlKey ) && e.code === 'ArrowLeft') {
                    this.$emit('backAction');
                    return e.preventDefault();
                }
            }
        );
    }
}
</script>


