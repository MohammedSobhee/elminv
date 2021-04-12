export default {
    name: 'clipboard',
    inserted(el, binding) {
        $(el).tooltip({
            container: 'body'
        });
        const copy = () => {
            const copiedNotice = binding.value.notice || 'Copied';
            el.setAttribute('data-original-title', copiedNotice);
            $(el).tooltip('show');
            let input = document.createElement('input');
            input.setAttribute('value', binding.value.text);
            document.body.appendChild(input);
            input.select();
            document.execCommand('copy');
            document.body.removeChild(input);
        };
        el.addEventListener('click', () => copy());
        el.$destroy = () => el.removeEventListener('click', copy);
    },
    unbind(el) {
        el.$destroy();
    }
};
