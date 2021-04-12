export default {
    components: {
        Notification: () => import(/* webpackChunkName: 'notification' */ '../components/common/Notification')
    },
    data() {
        return {
            notice: {
                text: '',
                color: '',
                bgColor: ''
            },
            hasErrors: false
        };
    },
    methods: {
        // Show Message
        // --------------------------------------------------------------------------
        notify(msg, color = 'success', duration = 8000) {
            const vm = this;
            const isObject = typeof vm.notice.text === 'object';
            const textColors = {
                success: 'text-success',
                danger: 'text-danger',
                warning: 'text-warning',
                highlight: 'text-primary',
                dark: 'text-dark'
            };
            const bgColors = {
                success: 'alert-success',
                danger: 'alert-danger',
                warning: 'alert-warning',
                highlight: 'alert-primary',
                dark: 'alert-dark'
            };

            if (msg.length) {
                vm.hasErrors = color === 'danger';
                if (isObject && typeof msg !== 'object') {
                    !vm.notice.text.find(str => str === msg) && vm.notice.text.push(msg);
                    //: (vm.notice.text = vm.notice.text.filter(str => str === msg))
                } else {
                    vm.notice.text = msg;
                }

                vm.notice.color = textColors[color];
                vm.notice.bgColor = bgColors[color];
            } else {
                vm.hasErrors = false;
                vm.notice.text = isObject ? [] : '';
                vm.notice.color = '';
                vm.notice.bgColor = '';
            }

            if (msg.length && duration)
                setTimeout(() => (vm.notice.text = isObject ? [] : ''), duration);
        }
    }
};
