//
// Jquery Prototype Extensions
// --------------------------------------------------------------------------
EDILJQ = {
    toggleDisplay: function(display, type, speed) {
        display = display || 'toggle';
        type = type || EDIL.toggleType;
        speed = speed || EDIL.toggleSpeed;

        if (display === 'hide') {
            switch (type) {
                case 'slide':
                    this.slideUp(speed);
                    break;
                case 'fade':
                    this.fadeOut(speed);
                    break;
                default:
                    this.hide();
            }
        } else if (display == 'show') {
            switch (type) {
                case 'slide':
                    this.slideDown(speed);
                    break;
                case 'fade':
                    this.fadeIn(speed);
                    break;
                default:
                    this.show();
            }
        } else {
            switch (type) {
                case 'slide':
                    this.slideToggle(speed);
                    break;
                case 'fade':
                    this.fadeToggle(speed);
                    break;
                default:
                    this.toggle();
            }
        }
        return this;
    }
};
