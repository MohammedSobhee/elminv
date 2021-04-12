(function($, window) {
    $(function() {
        EDIL.init();
    });

    var EDIL = {
        //
        // Settings
        // --------------------------------------------------------------------------
        toggleType: 'slide',
        toggleSpeed: 200,
        scrollSpeed: 500,
        isAdmin: $('.admin-bar').length || $('.elementor-editor-active').length || 0,

        //
        // Elements
        // --------------------------------------------------------------------------
        // Misc
        $inputs: $('input,select,textarea'),
        $page: $('html,body'),
        $sidebar: $('#sidebar'),
        $sidebarCourseware: $('#sidebar-courseware'),
        $footer: $('.footer'),
        $scrollTo: $('.scroll-to', '#content'),
        $navbar: $('#navbarMenu'),

        // Toggle list content
        $standardsLabel: $('.section-toggle-standards p strong', '#content'),
        $standardsList: $(
            '.section-toggle-standards .elementor-shortcode > ul, .section-toggle-standards .elementor-shortcode > ol',
            '#content'
        ),
        $standardsHeader: $('.section-toggle-standards h2', '#content'),
        $toggleListLabel: $('.section-toggle-standards strong', '#content'),
        $toggleList: $('.section-toggle-standards ul, .section-toggle-standards ol', '#content'),
        $expandListTrigger: $('.expand-lists', '#content'),

        // Challenge and Regular Text Box Content
        $regularHeader: $(
            '.section-note .elementor-widget:first-of-type, .section-action .elementor-widget:first-of-type',
            '#content'
        ),
        $regularContent: $(
            '.section-action .elementor-widget:not(:first-of-type), .section-note .elementor-widget:not(:first-of-type)',
            '#content'
        ),

        // Toggle Notes Content
        $teacherNotesWrapper: $('.acs'),
        $teacherNotes: $(
            '.acs .section-content-wrapper, .acs-inline-toggle .elementor-widget-container',
            '#content'
        ),

        //
        //  on Ready / Init
        // --------------------------------------------------------------------------
        init: function() {
            // Misc
            this.quickMisc();
            this.scrollToSection();
            this.selectBoxURLChange();
            this.selectSubmit();
            this.completedInputs();
            this.preventDefaults();
            //this.stickyTransitions();

            // Mobile Menu
            this.isMobile() && this.mobileSidebar();

            // Search toggle
            this.eduSearch();

            //
            // Toggle Content
            // --------------------------------------------------------------------------
            this.toggleContent();

            // Activate toggle list with header
            this.toggleListContent(this.$standardsLabel, this.$standardsList);

            // Notes Toggles
            this.toggleInnerContent(
                this.$teacherNotesWrapper,
                'Instructor note',
                'Instructor Notes'
            );
            // Expand All Content
            this.expandContent(this.$toggleList, this.$expandListTrigger, '');
            this.expandContent(this.$teacherNotes, 0, 'Instructor Notes');

            // Worksheet Find Modal
            this.worksheetFindModal();

            this.fixVideos();

            // Determine if objectives and standards should be shown
            this.acsObjectivesStandards();

            // Disable links for demo
            this.demoDisableLinks();

            // Posters
            this.posters();

            // Hide / Show Resourcees
            this.resources();

            // Mark announcement as read
            this.markReadAnnouncement();
        },

        //
        // Misc Fixes
        // --------------------------------------------------------------------------
        quickMisc: function() {
            // Activate bootstrap toggles
            $('[data-toggle="popover"]').popover({ html: true });
            $('[data-toggle="tooltip"]').tooltip();

            $('p').each(function() {
                // Remove empty WP <p> tags
                var $this = $(this);
                if ($this.html().replace(/\s|&nbsp;/g, '').length == 0) $this.remove();
            });

            // If admin, add highlights to toggled content
            this.isAdmin &&
                $('.section-toggle-header').length > 0 &&
                $('.section-toggle-header').addClass('highlight');

            // Add instructor note label to inline instructor notes
            $('.acs-inline.elementor-widget-text-editor', '#content').prepend(
                '<div class="label">Instructor note</div>'
            );

            // Destroy all instructor notes if student
            if (eduUser.role === 'student') {
                $('.acs, .acs-inline, .acs-button').remove();
            }

            // Add MakerTip label to Makertips
            // $('.content-tip.elementor-widget-text-editor p:first-child', '#content').prepend(
            //     '<strong>MakerTip&trade;:</strong> '
            // );

            // Turn on font awesome pseudo elements
            window.FontAwesomeConfig = {
                searchPseudoElements: true
            };
        },

        //
        // Select Box Change
        // --------------------------------------------------------------------------
        selectBoxURLChange: function() {
            $('.select-url-change').on('change', function() {
                $('form')
                    .find('.btn-action')
                    .prop('disabled', true);
                var url = $(this).val();
                window.location.href = url;
            });
        },

        selectSubmit: function() {
            $('.select-submit').on('change', function() {
                $(this).submit();
            });
        },

        //
        // Completed input elements
        // --------------------------------------------------------------------------
        completedInputs: function() {
            this.$inputs.on('blur', function() {
                $(this).each(function() {
                    $(this).val() && $(this).addClass('completed');
                });
            });
        },

        //
        // Search
        // --------------------------------------------------------------------------
        eduSearch: function() {
            var $search = $('input[type=search]'),
                $close = $('.search-clear'),
                $toggleSearch = $('.toggle-search'),
                $headerSearch = $('.header-search');

            this.isSmallScreen() && $search.length && $search.val().length > 0 && $close.show();

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
                    $headerSearch.animate(
                        { width: '5px' },
                        {
                            complete: function() {
                                EDIL.isMobile() && $headerSearch.hide().removeClass('search-show');
                            }
                        }
                    );

                    !EDIL.isMobile() &&
                        $toggleSearch.animate(
                            {
                                width: tWidth
                            },
                            {
                                complete: function() {
                                    $headerSearch.hide().removeClass('search-show');
                                    $toggleSearch.removeClass('focus');
                                }
                            }
                        );
                } else {
                    const searchWidth = EDIL.isMobile() ? '69%' : '160px';
                    $toggleSearch.addClass('focus');
                    $headerSearch.show();
                    $headerSearch.find('input[type=search]').focus();
                    $headerSearch.animate({
                        width: searchWidth
                    });
                    !EDIL.isMobile() &&
                        $toggleSearch.animate({
                            width: '265px'
                        });
                    $headerSearch.addClass('search-show');
                }
            });
        },

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
                        $sidebarToHide = $('.sidebar-menu-admin'),
                        sidebarScrollPosition = $('.masthead').height() + $header.height(),
                        $sidebarAdminMenu = $('#sidebar-admin');

                    $sidebarHiddenBtn.on('click', function() {
                        $sidebarToHide.slideToggle();
                    });
                }

                $(window).on('scroll', function() {
                    var $sidebarSticky = $('.sidebar-wrapper.sticky-transition'),
                        sidebarWidth = EDIL.$sidebar.width();

                    // Add sticky header if scrolled down and not logged in as admin
                    // $header.toggleClass(
                    //     'sticky-transition',
                    //     $content.height() > EDIL.$sidebar.height() &&
                    //         !EDIL.isMobile() &&
                    //         $(this).scrollTop() > 1 &&
                    //         !EDIL.isAdmin
                    // );
                    // If scrolled passed header, is not mobile, or logged in as admin
                    // Add sticky sidebar and assign normal sidebar left offset and width
                    if (
                        EDIL.$sidebar.length &&
                        $(this).scrollTop() > sidebarScrollPosition &&
                        $content.height() > EDIL.$sidebar.height()
                    ) {
                        $sidebarWrapper.addClass('sticky-transition');
                        $sidebarAdminMenu.length && $sidebarToHide.slideUp();
                        $sidebarAdminMenu.length && $sidebarHiddenBtn.slideDown();
                        EDIL.$sidebar.not('.sidebar-scroll').css('width', sidebarWidth);

                        // Remove sticky if scrolled back up to header
                    } else if ($sidebarSticky.length) {
                        $sidebarWrapper.removeClass('sticky-transition');
                        $sidebarAdminMenu.length && $sidebarToHide.slideDown();
                        $sidebarAdminMenu.length && $sidebarHiddenBtn.slideUp();
                        EDIL.$sidebar.attr('style', '');
                    }
                });
            }
        },

        //
        // Scroll to section
        // --------------------------------------------------------------------------
        scrollToSection: function() {
            var speed = this.scrollSpeed;
            this.$scrollTo.on('click', function() {
                $('html, body').animate(
                    {
                        scrollTop: $(this.hash).offset().top
                    },
                    speed
                );
            });

            $('.arrow-down').on('click', function() {
                $('html, body').animate(
                    {
                        scrollTop: $('div[data-elementor-type=post]').offset().top
                    },
                    EDIL.scrollSpeed
                );
            });

            // Add space for Elementor Menu Anchor link
            $(window).on('elementor/frontend/init', function() {
                elementorFrontend.on('components:init', function() {
                    elementorFrontend.utils.anchors.setSettings('scrollDuration', 0);
                });
                elementorFrontend.hooks.addFilter(
                    'frontend/handlers/menu_anchor/scroll_top_distance',
                    function(scrollTop) {
                        return scrollTop - 50;
                    }
                );
            });
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
        // Mobile Check
        // --------------------------------------------------------------------------
        isMobile: function() {
            return $('.navbar-toggler').css('display') !== 'none'; // eslint-disable-line
        },

        // Small Screen Check
        isSmallScreen: function() {
            return $(window).width() <= 768; // iPad or smaller
        },

        //
        // Mobile Side Bar
        // --------------------------------------------------------------------------
        mobileSidebar: function() {
            $('.dropdown-submenu a').on('click', function(e) {
                $(this)
                    .next('ul')
                    .toggle();
                e.stopPropagation();
                e.preventDefault();
            });
            var currentSectionStatus = 'Currently viewing',
                currentSectionLabel = this.$sidebar.find('#sidebar-header-courseware a').text(),
                $insertionLink = this.$navbar.find('li a:contains(' + currentSectionLabel + ')'),
                $insertionListItem = $insertionLink.parent(),
                $sidebarAssignments = this.$sidebar.find('#sidebar-assignments');

            // Put side bar assignments in content
            $sidebarAssignments.prependTo('#content');
            $sidebarAssignments.find('li a').each(function () {
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
                    .attr('aria-labelledby', 'currentSelection')
                    .addClass('dropdown-submenu')
                    .removeClass('sidebar-menu')
                    .appendTo($insertionListItem);
            }
            this.$sidebar.remove();
        },

        //
        // Toggle Content
        // --------------------------------------------------------------------------

        toggleContent: function() {
            var $toggleClass = $('.section-toggle');
            $toggleClass.find('.section-toggle-header').each(function() {
                //var $content = $(this).siblings('.section-toggle-content'),
                var $content = $(this)
                    .closest('.section-toggle')
                    .find('.section-toggle-content');

                $(this).on('click', function(e) {
                    $(this).toggleClass('highlight'); //.removeClass('unread');
                    $content.toggleClass('open');

                    // Move scroll to e.target and toggle
                    $content.slideToggle({
                        duration: EDIL.toggleSpeed,
                        progress: function() {
                            //var scTop = EDIL.isMobile() ? 20 : 50;
                            var scTop = 20;
                            EDIL.$page.on(
                                'scroll mousedown wheel DOMMouseScroll mousewheel keyup touchmove',
                                function() {
                                    EDIL.$page.stop();
                                }
                            );
                            EDIL.$page.animate(
                                {
                                    scrollTop: $(e.target).offset().top - scTop
                                },
                                50
                            );
                        }
                    });

                    $toggleClass
                        .find('.section-toggle-content')
                        .not($content)
                        .each(function() {
                            if ($(this).is(':visible')) {
                                $(this)
                                    .removeClass('open')
                                    .hide();
                                //$(this).removeClass('open').slideUp(EDIL.toggleSpeed);
                                $(this)
                                    .prev('.section-toggle-header')
                                    .removeClass('highlight');
                            }
                        });
                });
            });
        },

        //
        // Toggle List Content
        // --------------------------------------------------------------------------
        toggleListContent: function(label, content) {
            var speed = this.toggleSpeed;
            label.on('click', function() {
                var $nextUL = $(this)
                    .parent()
                    .next();
                label.removeClass('highlight');
                $(this).toggleClass('highlight');
                $nextUL.css('display') == 'block' && $(this).removeClass('highlight');
                content.not($nextUL).slideUp(speed);
                $nextUL.slideToggle(speed);
            });
        },

        //
        // Add toggled content inside a wrapper (Teacher Notes)
        // ------------------------------------------------------------------------
        toggleInnerContent: function(wrapper, label, shortlabel) {
            if (this.isAdmin) {
                wrapper.find('.section-content-wrapper').show();
                wrapper.addClass('open');
            }

            wrapper.prepend('<div class="label">' + label + '</div>');
            if (wrapper.length > 0 && eduUser.role !== 'student') {
                var btn = `<button type="button" class="btn btn-primary btn-block btn-sm expand-content">Expand all ${shortlabel}</button>`;
                if(this.isMobile()) {
                    this.$navbar.prepend(btn);
                    $('.navbar .expand-content').on('click', function() {
                        $('.navbar-collapse').collapse('hide');
                    });
                } else {
                    this.$sidebarCourseware.after(btn);
                }
            }

            wrapper.on('click', function() {
                $(this).toggleClass('open');
                if ($(this).hasClass('acs-inline-toggle')) {
                    $(this)
                        .find('.elementor-widget-container')
                        .slideToggle(EDIL.toggleSpeed);
                } else {
                    $(this)
                        .find('.section-content-wrapper')
                        .slideToggle(EDIL.toggleSpeed);
                }
            });
        },

        //
        // Expand all hidden content on page (Careers)
        // --------------------------------------------------------------------------
        expandContent: function(content, tgl, tp) {
            // defaults
            var toggle = tgl.length > 0 ? tgl : $('.expand-content'),
                speed = EDIL.toggleSpeed,
                type = tp.length > 0 ? tp : '';

            toggle.on('click', function(e) {
                e.preventDefault();
                var label = content.prev().find('strong');
                // If expanded, collapse
                if (content.filter(':visible').length > 0) {
                    content.parents('.elementor-top-section').removeClass('open');
                    $(this)
                        .html('Expand all ' + type)
                        .removeClass('collapsed');
                    content.slideUp(speed);
                    if (label.length > 0) {
                        label.each(function() {
                            $(this).removeClass('highlight');
                        });
                    }
                    // If collapsed, expand
                } else {
                    content.parents('.elementor-top-section').addClass('open');
                    content.parents().addClass('open'); // acs-inline-toggle
                    $(this)
                        .html('Collapse all ' + type)
                        .addClass('collapsed');
                    content.slideDown(speed);
                    if (label.length > 0) {
                        label.each(function() {
                            $(this).addClass('highlight');
                        });
                    }
                }
            });
        },

        //
        // Worksheet Find
        // --------------------------------------------------------------------------
        worksheetFindModal: function() {
            $('.find-worksheet').on('click', function(e) {
                e.preventDefault();
                var $modal = $('#worksheetFindModal'),
                    $modalIFrame = $modal.find('iframe'),
                    $modalTitle = $modal.find('.modal-title'),
                    $modalHeader = $modal.find('.modal-header');

                $modalHeader.addClass('border-0');
                $modalTitle.text('Retrieving...');
                $modal.modal('toggle');

                var url = this.href,
                    checkurl = url.replace('find', '') + 'pcount';

                $.get(checkurl, function(data) {
                    if (data.project_count === 1) {
                        $modal.modal('hide');
                        window.top.location.href =
                            '/worksheets/' + data.worksheet_id + '/' + data.projects[0].id;
                    } else {
                        $modalHeader.removeClass('border-0');
                        $modalTitle.text(
                            data.project_count === 0
                                ? 'Add a project to begin:'
                                : 'Select a project for the worksheet:'
                        );
                        $modal.find('iframe').attr('src', url);
                        $modalIFrame.fadeIn();
                        $modal.find('.modal-status').fadeOut();
                    }
                });
            });
        },

        //
        //
        // --------------------------------------------------------------------------
        acsObjectivesStandards: function() {
            if (eduUser.settings.objectives === 1) {
                $('.section-objectives').removeClass('acst');
            }
            if (eduUser.settings.standards === 1) {
                $('.section-toggle-standards').removeClass('acst');
            }
        },

        //
        // Fix Videos
        // --------------------------------------------------------------------------
        fixVideos: function() {
            // Vimeo / Mashable
            var $vimVideos = $('iframe[src*="https://player.vimeo.com"].video');
            //$vEl = $('.video');

            if ($vimVideos.length > 0) {
                $vimVideos.each(function() {
                    if (
                        $(this)
                            .parent()
                            .hasClass('video-frame') &&
                        $(this).hasClass('elementor-video-iframe')
                    ) {
                        $(this).wrap('<div class="video-frame"></div>');
                    }
                });
            }
        },

        //
        // Posters
        // --------------------------------------------------------------------------
        posters: function() {
            var sourceSwap = function() {
                var $this = $(this);
                var newSource = $this.data('flip');
                $this.data('flip', $this.attr('src'));
                $this.attr('src', newSource);
            };

            $(function() {
                $('.poster[data-flip]').each(function() {
                    var $this = $(this);
                    new Image().src = $this.data('flip');

                    const url = $this.data('lightbox');
                    if(url.length) {
                        const title = $this.attr('alt');
                        let eleData = btoa(`{"url":"${url}","type":"image","title":"${title}"}`);
                        $this.wrap(
                            `<a href="#elementor-action%3Aaction%3Dlightbox%26settings%3D${eleData}">`
                        );
                    }
                }).hover(sourceSwap, sourceSwap);
            });
        },

        // Disable all links in #content if demo school
        demoDisableLinks: function() {
            if(eduUser.school_id === '93' && $('body:not(.parent-pageid-18):not(.parent-pageid-961):not(.page-id-961):not(.page-id-18):not(.page-id-934):not(.page-id-16)').length) {
                $('#content a').each(function() {
                    var link = $(this).attr('href');
                    if (/^((?!.*step-1|.*25\/find|.*a-setting|.*number-1|.*4-script-storming|.*character-traits|.*7-success-qualities|.*9-step-method(?![/]?[a-z]+)).+)/g.test(link)) {
                        $(this).removeAttr('href');
                        $(this).attr('data-toggle', 'tooltip');
                        $(this).attr('title', 'Disabled for demo');
                        $(this).tooltip();
                    }
                });
            }
        },


        //
        // Hide Show Resource sections based on class_type
        // -------------------------------------------------------------------------
        resources: function() {
            if (eduUser.class_type > 2) { // High School
                $('#resource-chipper').hide();
                $('#resource-bugreport').show();
            } else { // Elementary
                $('#resource-bugreport').hide();
                $('#resource-chipper').show();
            }
        },

        //
        // prevent default on grayed out links
        // --------------------------------------------------------------------------
        deactivateLinks: function(link) {
            $(link + ', ' + link + ' a').on('click', function(e) {
                e.preventDefault();
            });
            $(link + ' a').removeClass('loadbox-norefresh cboxElement');
        },

        //
        // Mark announcement as read
        // --------------------------------------------------------------------------
        markReadAnnouncement: function() {
            $('.announcements').on('mouseover', function() {
                var $title = $(this).find('.announcements-title');
                //if($title.hasClass('wp-announcements-unread')) {
                setTimeout(function() {
                    $title.removeClass('announcements-unread');
                    $title.find('.announcements-unread-icon').fadeOut('fast');
                }, 2000);
                //}
            });
        }
    };
})(window.jQuery, window);
