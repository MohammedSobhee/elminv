import { elementExists } from './functions/utils';

(function($, window, document) {
    $(function() {
        EDILA.init();
    });

    var EDILA = {
        //
        // Settings
        // --------------------------------------------------------------------------
        toggleType: 'slide',
        toggleSpeed: 200,
        scrollSpeed: 500,
        isAdmin: $('.admin-bar').length || $('.elementor-editor-active').length || 0,

        // Misc
        $navbar: $('#navbarMenu'),
        $sidebar: $('.sidebar'),
        $footer: $('.footer'),
        $sidebarDropMenu: $('#sidebar-drop-menu'),

        init: function() {
            this.quickMisc();
            this.eduSearch();
            !this.isMobile() && this.sidebarDropMenu();
            this.isMobile() && this.mobileSidebar();
            this.preventDefaults();
            this.fixBreadCrumbs();
            //this.stickyTransitions();
            this.scrollToSection();
            this.selectBoxURLChange();
            this.selectSubmit();
            this.confirmDelete();
            this.pageLinkOpenModal();
        },

        isMobile: function() {
            return $('.navbar-toggler').css('display') !== 'none'; // eslint-disable-line
        },

        // Small Screen Check
        isSmallScreen: function() {
            return $(window).width() <= 768; // iPad width or smaller
        },

        //
        // Prevent Defaults
        // --------------------------------------------------------------------------
        preventDefaults: function() {
            $('a[href^="#"]').on('click', function(e) {
                e.preventDefault();
            });
        },

        //
        // Misc
        // --------------------------------------------------------------------------
        quickMisc: function() {
            // Activate bootstrap toggles
            $('[data-toggle="tooltip"]').tooltip();

            $('.toggle-tooltip').on('mouseover', function() {
                const delay = $(this).data('delay');
                $(this).tooltip({
                    delay: {
                        show: delay || 1000,
                        hide: 0
                    }
                });
            });

            $('[data-toggle="popover"]').popover({ html: true });

            // Form section toggle
            $('.form-toggle').on('click', function() {
                $('#' + $(this).data('formtoggle')).fadeToggle('fast');
            });

            // Create dismissable popover
            $('.popover-dismiss').popover({
                trigger: 'focus'
            });

            $('.clear-cancel').on('click', function() {
                $($(this).data('popover')).popover('hide');
            });
        },

        //
        // Mobile Side Bar (re-Build Sidebar into Bootstrap nav)
        // --------------------------------------------------------------------------
        mobileSidebar: function() {
            var currentSectionStatus = 'Last viewed',
                currentSectionLabel = this.$sidebar.find('#sidebar-header-courseware a').text(),
                $insertionLink = this.$navbar.find('li a:contains(' + currentSectionLabel + ')'),
                $insertionListItem = $insertionLink.parent(),
                $sidebarAssignments = this.$sidebar.find('#sidebar-assignments');

            // Put side bar assignments in content
            $sidebarAssignments.prependTo('#content');
            $sidebarAssignments.find('li a').each(function() {
                $(this).html('Additional Assignment: ' + $(this).text());
            });

            // Add Bootstrap's .nav-link to all appended items
            this.$sidebar.find('ul li a').addClass('nav-link');

            // Check if courseware menu exists
            if (currentSectionLabel.length > 0) {
                // Add insersion parent link back into the list of appended nav items as the
                // existing link will be used for Bootstrap's dropdown trigger
                this.$sidebar
                    .find('#sidebar-courseware')
                    .prepend(
                        '<li class="menu-item dropdown-menu-item"><strong><a class="nav-link" href="' +
                            $insertionLink.attr('href') +
                            '">' +
                            currentSectionLabel +
                            '</a></strong></li>'
                    );
                // Label the insertion link's (dropdown trigger) status (i.e Last viewed, Currently viewed)
                $insertionLink.html(currentSectionLabel + ' <i>(' + currentSectionStatus + ')</i>');

                $insertionLink.attr('href', '#');
                $insertionListItem.find('a'); //.addClass('dropdown-toggle');
                $insertionListItem.addClass('dropdown');

                // Add the rest of the dropdown classes and lastly, append to insertion list item
                this.$sidebar.find('#sidebar-courseware li'); //.addClass('dropdown-menu-item');
                this.$sidebar
                    .find('#sidebar-courseware')
                    //.attr('aria-labelledby', 'currentSelection')
                    .addClass('dropdown-submenu')
                    .removeClass('sidebar-menu')
                    .appendTo($insertionListItem);
            }
            this.$sidebar.remove();
        },
        //
        // Scroll to section
        // --------------------------------------------------------------------------
        scrollToSection: function() {
            $('.scroll-to').on('click', function() {
                $('html, body').animate({
                    scrollTop: $(this.hash).offset().top
                }, 500);
            });
        },

        //
        // Sidebar Dropdown
        // --------------------------------------------------------------------------
        sidebarDropMenu: function() {
            if (this.isSmallScreen()) return;

            var $btn = $('#sidebar-drop-menu-btn');
            var $btnHiddenMenu = $('.sidebar-hidden-menu-btn');
            var $dropMenu = $('.sidebar-drop-menu');
            $btn.on('click', function() {
                $(this).toggleClass('active');
                $dropMenu.slideToggle('fast');
                !EDILA.isMobile() && $('.main-overlay').toggle();
            });
            $dropMenu.width($('.sidebar').width());
            $(document).on('click', function(e) {
                if (
                    e.target.id.indexOf('button') === -1 &&
                    e.target.id !== 'sidebar-drop-menu-btn'
                ) {
                    $btn.removeClass('active');
                    $dropMenu.slideUp('fast');
                    !EDILA.isMobile() && $('.main-overlay').hide();
                }
            });
            $btnHiddenMenu.on('click', function() {
                $(this).parent().find('.sidebar-hidden-menu-btn').removeClass('active');
                $(this).parent().find('.sidebar-hidden-menu').slideUp('fast');
                $(this).toggleClass('active');
                $(this).next(':hidden').slideToggle('fast');
            });
        },

        //
        // Go to URL select
        // --------------------------------------------------------------------------
        selectBoxURLChange: function() {
            function gotoURL(url) {
                if (url !== 'prevent') {
                    if (window.self !== window.top) window.top.location.href = url;
                    else window.location.href = url;
                }
            }

            $('.select-url-change').on('change', function() {
                const url = $(this).val();
                gotoURL(url);
            });
            $('.btn-url-change').on('click', function() {
                const url = $('.' + $(this).data('target')).val();
                gotoURL(url);
            });
        },

        //
        // Fix breadcrumbs on mobile
        // --------------------------------------------------------------------------
        fixBreadCrumbs: function() {
            // Fix breadcrumbs
            if ($(window).width() <= 992) {
                if ($('.breadcrumb').length) {
                    const $lastItem = $('.breadcrumb-item:last-of-type');
                    $lastItem.text().length > 40 && $lastItem.addClass('breadcrumb-item-break');
                }
            }
        },

        //
        // Select submit
        // --------------------------------------------------------------------------
        selectSubmit: function() {
            $('.select-submit').on('change', function() {
                $(this).submit();
            });
        },

        //
        // Simple Confirm Delete
        // --------------------------------------------------------------------------
        confirmDelete: function() {
            $('.confirm-delete').on('click', function() {
                if (window.confirm('Are you sure? This cannot be undone.')) {
                    window.location.href = $(this).data('link');
                }
            });
        },

        //
        // Search animation
        // --------------------------------------------------------------------------
        eduSearch: function() {
            var $search = $('input[type=search]'),
                $close = $('.search-clear'),
                $toggleSearch = $('.toggle-search'),
                $headerSearch = $('.header-search');

            EDILA.isSmallScreen() && $search.length && $search.val().length > 0 && $close.show();

            $search.on('keydown change focus', function() {
                var $tclose = $(this).siblings('.search-clear');
                $(this).val().length > 0 && $tclose.show();
                $(this).val().length < 2 && $tclose.hide();
            });

            $close.on('click', function() {
                $search.val('');
            });

            var tWidth = $toggleSearch.outerWidth();
            $('.toggle-search a').on('click', function() {
                if ($headerSearch.hasClass('search-show')) {
                    $headerSearch.animate({ width: '5px'}, {
                        complete: function() {
                            EDILA.isMobile() && $headerSearch.hide().removeClass('search-show');
                        }
                    });

                    !EDILA.isMobile() &&
                        $toggleSearch.animate({ width: tWidth }, {
                            complete: function() {
                                $headerSearch.hide().removeClass('search-show');
                                $toggleSearch.removeClass('focus');
                            }
                        });
                } else {
                    const searchWidth = EDILA.isMobile() ? '69%' : '160px';
                    $toggleSearch.addClass('focus');
                    $headerSearch.show();
                    $headerSearch.find('input[type=search]').focus();
                    $headerSearch.animate({
                        width: searchWidth
                    });
                    !EDILA.isMobile() &&
                        $toggleSearch.animate({
                            width: '265px'
                        });
                    $headerSearch.addClass('search-show');
                }
            });
        },

        //
        // Open blade component modal automatically upon landing on page
        // --------------------------------------------------------------------------
        pageLinkOpenModal: function() {
            if (window.location.hash.indexOf('#modal') !== -1) {
                var modal = window.location.hash.replace('#modal-', '');

                elementExists('#' + modal + 'Modal').then(function() {
                    const $modal = $('#' + modal + 'Modal');
                    $modal.modal('show');
                    $modal.on('shown.bs.modal', function() {
                        $(this).find('.modal-body :input:enabled:visible:first').focus();
                    });
                });
            }
        }
    };
})(window.jQuery, window, document);
