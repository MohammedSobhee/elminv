            // data
            tooltipsNum: 1000, // Number of times to show a 'description' tooltip
            // directive
            bind: (el, binding, vnode) => {
                if (vnode.context.cookie.tooltip[binding.arg] < vnode.context.tooltipsNum) {
                    const vm = vnode.context;
                    $(el)
                        .tooltip({
                            title: () =>
                                vm.cookie.tooltip[binding.arg] < vm.tooltipsNum && binding.value,
                            trigger: 'hover focus',
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
            }

        //methods
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
