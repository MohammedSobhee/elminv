export default {
    name: 'closeEsc',
    bind(el, binding, vnode) {
        const close = e => e.code === 'Escape' && vnode.context.closeAssignment();
        document.addEventListener('keydown', e => close(e));
        el.$destroy = () => document.removeEventListener('keydown', close);
    },
    unbind(el) {
        el.$destroy()
    }
};
