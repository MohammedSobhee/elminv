import { getFullName, capitalizeString } from '../functions/utils';

export default {
    filters: {
        capitalize(value) {
            return capitalizeString(value);
        }
    },
    directives: {
        tooltip: {
            // inserted: (el, binding) => {
            //     let selector = $(el);
            //     if (el.classList.contains('multiselect')) {
            //         selector = $(el).find('.multiselect__placeholder');
            //     }
            //     selector.tooltip({
            //         title: binding.value,
            //         delay: {
            //             show: 2000,
            //             hide: 500
            //         }
            //     });
            // }
            bind: (el, binding, vnode) => {
                if (vnode.context.cookie.tooltip[binding.arg] < vnode.context.tooltipsNum) {
                    const vm = vnode.context;
                    $(el)
                        .tooltip({
                            title: () =>
                                vm.cookie.tooltip[binding.arg] < vm.tooltipsNum && binding.value,
                            trigger: 'hover focus',
                            html: true,
                            delay: {
                                show: 2000,
                                hide: 500
                            }
                        })
                        .on('hidden.bs.tooltip', function() {
                            $(this).tooltip('dispose');
                        })
                        .on('shown.bs.tooltip', () => vm.setTooltipCookie(vm.cookie, binding.arg));
                }
            },
            unbind: el => {
                $(el).tooltip('dispose');
            }
        },
        btooltip: {
            inserted: (el, binding) => {
                let title = el.getAttribute('title');
                let show = 1500;
                let selector = $(el);

                if (el.classList.contains('multiselect')) {
                    selector = $(el).find('.multiselect__placeholder');
                }

                if (typeof binding.value === 'object') {
                    typeof binding.value.title !== 'undefined' && (title = binding.value.title);
                    typeof binding.value.delay !== 'undefined' && (show = binding.value.delay);
                }

                if (title) {
                    selector.tooltip({
                        title: title,
                        html: true,
                        delay: {
                            show: show,
                            hide: 500
                        }
                    });
                }
            },
            unbind: el => {
                $(el).tooltip('dispose');
            }
            // update: (el, binding) => {
            //     $(el).tooltip('show');
            // }
        },
        bpopover: {
            inserted: (el, binding) => {
                if (binding.value) {
                    $(el).popover({
                        //placement: binding.arg || 'top',
                        html: true,
                        title:
                            el.getAttribute('data-popover-title') +
                            '<i class="popover-header-icon fas fa-times" title="Close"></i>',
                        content: binding.value,
                        trigger: 'click|focus'
                    });
                }
                // .on('show.bs.popover', function (e) {
                //     $('[data-original-title]').not(e.target).removeData('bs.popover').popover('dispose');
                // });
            },
            unbind: el => {
                $(el).popover('dispose');
            }
        }
    },

    data() {
        return {
            tooltipsNum: 1, // Number of times to show a 'description' tooltip
            user_id: 0
        };
    },

    created() {
        let eduData = document.head.querySelector('meta[name="eduiland-data"]');
        if (eduData) {
            eduData = JSON.parse(window.atob(eduData.content));
            this.user_id = +eduData.user_id;
            this.user_role = eduData.user_role;
            this.teacher_name = eduData.teacher_name;
            this.user_class_id = eduData.class_id;
        }
    },
    methods: {
        //
        // Alert
        // --------------------------------------------------------------------------
        alert(value) {
            alert(value);
        },

        //
        // Get full name
        // --------------------------------------------------------------------------
        getFullName(first, last, reverse) {
            return getFullName(first, last, reverse);
        },
        //
        // Sanitize File name
        // --------------------------------------------------------------------------
        sanitizeFileName(fileName) {
            return fileName.replace(/[/\\?%*:|"<>]/g, '-');
        },
        //
        // Cookies
        // --------------------------------------------------------------------------
        setCookie(obj, props = null) {
            const vm = this;
            Cookies.remove(`eduiland${this.user_id}_${this.$options.name.toLowerCase()}`); // TEMP
            props && Object.assign(obj, { ...props });
            const cookieVue = vm.$options.name.toLowerCase() + vm.user_id;
            vm.eduilandCookie = vm.eduilandCookie === undefined ? {} : vm.eduilandCookie;
            vm.eduilandCookie[cookieVue] = obj;
            Cookies.set('eduiland', JSON.stringify(vm.eduilandCookie), 'false');
        },

        getCookie() {
            const vm = this,
                cookie = Cookies.get('eduiland');
            const cookieVue = vm.$options.name.toLowerCase() + vm.user_id;
            vm.eduilandCookie = cookie === undefined ? {} : JSON.parse(cookie);
            return vm.eduilandCookie[cookieVue] === undefined ? {} : vm.eduilandCookie[cookieVue];
        },

        resetCookie(obj) {
            Object.keys(obj).forEach(k => k !== 'tooltip' && (obj[k] = null));
            this.setCookie(obj);
        },

        setTooltipCookie(cookieObj, tip) {
            if (cookieObj.tooltip[tip] < this.tooltipsNum) {
                ++cookieObj.tooltip[tip];
                this.setCookie(cookieObj);
            }
        },
        setupTooltipCookie(cookieObj, tips) {
            if (typeof cookieObj.tooltip === 'undefined') {
                cookieObj.tooltip = tips;
            }
        }
    }
};
