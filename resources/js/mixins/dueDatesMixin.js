export default {
    directives: {
        closeDueDate: {
            bind: (el, binding, vnode) => {
                const closeEsc = e => e.code === 'Escape' && vnode.context.toggleDueDate();
                const clickOut = e => {
                    const noClickOutClasses = [
                        'btn-duedate',
                        // 'list-group-item-duedate',
                        // 'form-inline',
                        // 'form-flatpickr',
                        // 'delete-action',
                        'animated'
                    ];
                    // Prevent clickOut if target is the el itself,
                    // target is contained within the el, specitic classes not caught by contains,
                    // and calender contents
                    if (!(
                        el == e.target ||
                        el.contains(e.target) ||
                        noClickOutClasses.some(cn => e.target.classList.contains(cn)) ||
                        e.target.closest('.duedate-wrapper') ||
                        e.target.closest('.flatpickr-calendar')
                    )) {
                        vnode.context.toggleDueDate();
                    }
                };

                document.addEventListener('mousedown', e => clickOut(e));
                document.addEventListener('keydown', e => closeEsc(e));

                el.$destroy = () => {
                    document.removeEventListener('mousedown', clickOut);
                    document.removeEventListener('keydown', closeEsc);
                };
            },
            unbind: el => el.$destroy()
        }
    },

    components: {
        DueDates: () => import(/* webpackChunkName: 'due-dates' */ '../components/common/DueDates')
    },

    data() {
        return {
            dueDate: 0
        }
    },

    methods: {
        //
        // Show Due Date
        // --------------------------------------------------------------------------
        toggleDueDate(fileID = null) {
            this.dueDate = this.dueDate === fileID ? 0 : fileID;
        }
    }
};
