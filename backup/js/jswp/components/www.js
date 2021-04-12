//
// Misc Fixes
// --------------------------------------------------------------------------
EDIL.miscFix = function() {
    $('p').each(function() { // Remove empty WP <p> tags
        var $this = $(this);
        if($this.html().replace(/\s|&nbsp;/g, '').length == 0)
            $this.remove();
    });

    // If admin, add highlights to toggled content
    (this.isAdmin && $('.section-toggle-header').length > 0)
    && $('.section-toggle-header').addClass('highlight');

    // Add instructor note label to inline instructor notes
    $('.acs-inline.elementor-widget-text-editor', '#content').prepend('<div class="label">Instructor note</div>');

    // Add MakerTip label to Makertips
    $('.content-tip.elementor-widget-text-editor p:first-child', '#content').prepend('<strong>MakerTip:</strong> ');

    // Turn on font awesome pseudo elements
    window.FontAwesomeConfig = {
        searchPseudoElements: true
    }


};

//
// Style Blog Navigation
// --------------------------------------------------------------------------
EDIL.styleBlogNav = function () {
    var $item = $('.nav-blog-item');
    $item.each(function() {
        var title = $(this).find('.nav-blog-title').text();
        $(this).find('option:first-child').text(title);

    });
};


//
// Select Box URL Change
// --------------------------------------------------------------------------
EDIL.selectBoxURLChange = function() {
    $('.select-url-change').on('change', function() {
        $('form').find('.btn-action').prop('disabled', true);
        var url = $(this).val();
        window.location.href = url;
    });
};

//
// Search
// --------------------------------------------------------------------------
EDIL.eduSearch = function() {
    var $search = $('input[type=search]'),
        $close = $('.search-clear'),
        $toggleSearch = $('.toggle-search'),
        $headerSearch = $('.header-search');

    EDIL.isSmallScreen() &&
        $search.length &&
        $search.val().length > 0 &&
        $close.show();

    $search.on('keydown change focus', function() {
        var $tclose = $(this).siblings('.search-clear');
        $(this).val().length > 0 && $tclose.show();
        $(this).val().length < 2 && $tclose.hide();
    });

    $close.on('click', function() {
        $search.val('');
    });


    var tWidth = $toggleSearch.width() + 20;
    $('.toggle-search a').on('click', function() {



        if ($headerSearch.hasClass('search-show')) {

            $headerSearch.animate({
                width: '5px'
            }, {
                complete: function() {
                    EDIL.isMobile() && $headerSearch.hide().removeClass('search-show');
                }
            });

            !EDIL.isMobile() && $toggleSearch.animate({
                'width': tWidth
            }, {
                complete: function() {
                    $headerSearch.hide().removeClass('search-show');
                    $toggleSearch.removeClass('focus');
                }
            });

        } else {
            const searchWidth = EDIL.isMobile() ? '69%' : '160px';
            $toggleSearch.addClass('focus');
            $headerSearch.show();
            $headerSearch.find('input[type=search]').focus();
            $headerSearch.animate({
                width: searchWidth
            });
            !EDIL.isMobile() && $toggleSearch.animate({
                'width': '265px'
            });
            $headerSearch.addClass('search-show');
        }

    });
};


//
// Completed input elements
// --------------------------------------------------------------------------
EDIL.completedInputs = function() {
    this.$inputs.on('blur', function() {
        $(this).each(function() {
            $(this).val() && $(this).addClass('completed');

        });
    });
};
