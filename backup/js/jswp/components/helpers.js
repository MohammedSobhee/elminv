//
// Scroll to section
// --------------------------------------------------------------------------
EDIL.scrollToSection = function() {
    var speed = this.scrollSpeed;
    this.$scrollTo.on('click', function() {
        $('html, body').animate(
            {
                scrollTop: $(this.hash).offset().top
            },
            speed
        );
    });

    $('.arrow-down').on('click', function () {
        $('html, body').animate({
            scrollTop: $('div[data-elementor-type=post]').offset().top
        }, EDIL.scrollSpeed);
    });

    // Add space for Elementor Menu Anchor link
    $(window).on('elementor/frontend/init', function() {
        elementorFrontend.hooks.addFilter('frontend/handlers/menu_anchor/scroll_top_distance', function(scrollTop) {
            return scrollTop - 50;
        });
    });

};

//
// Prevent Defaults
// --------------------------------------------------------------------------
EDIL.preventDefaults = function() {
    $('a[href^="#"]').on('click', function(e) {
        e.preventDefault();
    });
};

//
// Show Notices
// --------------------------------------------------------------------------
EDIL.showNotice = function(text, type) {
    type = type || 'success';
    var notice = '<div class="' + type + '-notice small">' + text + '</div>';
    return notice;
};

// Remove notices
EDIL.removeNotices = function() {
    var $notices = $('[class*="-notice"]');
    $notices.length && $notices.hide();
};

// Close section popup
EDIL.closeSectionPopup = function() {
    $('.close-section-popup').on('click', function() {
        $('.popop-section').fadeOut();
    });
};
