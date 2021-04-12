//
        // Sticky Sidebar And Header
        // --------------------------------------------------------------------------
        stickyTransitions: function() {
            if (!this.isAdmin && !this.isMobile()) {
                if (this.$sidebar.length) {
                    var $header = $('.header, .masthead'),
                        $content = $('#content'),
                        $sidebarWrapper = $('.sidebar-wrapper'),
                        $sidebarHiddenBtn = $('#sidebar-hidden-menu-btn'),
                        $sidebarToHide = $('.sidebar-coursepages'),
                        sidebarScrollPosition = $('.masthead').height() + $header.height(),
                        sidebarDropBtnScrollPosition = sidebarScrollPosition,
                        $sidebarDropBtn = $('#sidebar-drop-menu-btn'),
                        $sidebarDropMenu = $('.sidebar-drop-menu'),
                        $sidebarAdminMenu = $('#sidebar-admin');

                    $sidebarHiddenBtn.on('click', function() {
                        $sidebarToHide.slideToggle('fast');
                    });
                }

                $(window).on('scroll', function() {
                    var $sidebarSticky = $('.sidebar-wrapper.sticky-transition'),
                        sidebarWidth = EDILA.$sidebar.width();

                    // Add sticky header if scrolled down and not logged in as admin
                    // $header.toggleClass(
                    //     'sticky-transition',
                    //     $content.height() > EDILA.$sidebar.height() &&
                    //         !EDILA.isMobile() &&
                    //         $(this).scrollTop() > 1 &&
                    //         !EDILA.isAdmin
                    // );
                    // If scrolled passed header, is not mobile, or logged in as admin
                    // Add sticky sidebar and assign normal sidebar left offset and width
                    if (
                        (EDILA.$sidebar.length && $sidebarDropBtn.length && $(this).scrollTop() > sidebarDropBtnScrollPosition) ||
                        (EDILA.$sidebar.length &&
                        $(document).height() > $(window).height() + 100 &&
                        $(this).scrollTop() > sidebarScrollPosition &&
                        $content.height() > EDILA.$sidebar.height())
                    ) {
                        $sidebarDropMenu.length && $sidebarDropMenu.addClass('sticky-transition')
                        $sidebarWrapper.addClass('sticky-transition');
                        $sidebarAdminMenu.length && $sidebarToHide.slideUp('fast');
                        $sidebarAdminMenu.length && $sidebarHiddenBtn.slideDown('fast');
                        EDILA.$sidebar.not('.sidebar-scroll').add($sidebarDropBtn).css('width', sidebarWidth);

                        // Remove sticky if scrolled back up to header
                    } else if ($sidebarSticky.length) {
                        $sidebarDropMenu.length && $sidebarDropMenu.removeClass('sticky-transition');
                        $sidebarWrapper.removeClass('sticky-transition');
                        $sidebarAdminMenu.length && $sidebarToHide.slideDown('fast');
                        $sidebarAdminMenu.length && $sidebarHiddenBtn.slideUp('fast');
                        EDILA.$sidebar.attr('style', '');
                    }
                });
            }
        },
