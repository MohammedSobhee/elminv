export default {
    name: 'stopFileDrag',
    bind(el, binding, vnode) {
        // Ensure dropfiles overlay goes away since dragleave fails
        const stop = () => vnode.context.dragging = false;
        document.addEventListener('mousedown', () => stop());
        el.$destroy = () => document.removeEventListener('mousedown', stop);
    },
    unbind(el) {
        el.$destroy()
    }
};
