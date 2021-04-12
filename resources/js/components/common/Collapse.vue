<template>
    <transition
        name="collapse"
        @enter="enter"
        @after-enter="afterEnter"
        @leave="leave">
        <slot />
    </transition>
</template>

<script>
export default {
    name: 'Collapse',
    methods: {
        enter(el) {
            // https://markus.oberlehner.net/blog/transition-to-height-auto-with-vue/
            let width = getComputedStyle(el).width;

            // Set properties to get height but don't impact
            // other elements (absolute/hidden)
            Object.assign(el.style, {
                width: width,
                position: 'absolute',
                visiblity: 'hidden',
                height: 'auto'
            });
            const height = getComputedStyle(el).height;

            // Set height, etc back to 0
            Object.assign(el.style, {
                width: null,
                position: null,
                visiblity: null,
                height: 0
            });
            // Force repaint to make sure the
            // animation is triggered correctly.
            getComputedStyle(el).height; // eslint-disable-line no-unused-expressions
            // Trigger the animation.
            // We use `setTimeout` because we need
            // to make sure the browser has finished
            // painting after setting the `height`
            // to `0` in the line above.
            setTimeout(() => {
                el.style.height = height;
            });
        },
        afterEnter(el) {
            el.style.height = 'auto';
        },
        leave(el) {
            const height = getComputedStyle(el).height;
            el.style.height = height;
            // Force repaint to make sure the
            // animation is triggered correctly.
            getComputedStyle(el).height; // eslint-disable-line no-unused-expressions
            setTimeout(() => {
                el.style.height = 0;
            });
        }
    }
};
</script>
<style scoped>
* {
    will-change: height;
    transform: translateZ(0);
    backface-visibility: hidden;
    perspective: 1000px;
}
</style>
