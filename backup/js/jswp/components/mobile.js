//
// Mobile Check
// --------------------------------------------------------------------------
EDIL.isMobile = function() {
    //return $('.desktop-hide').css("display") !== "none";  // eslint-disable-line
    return $('.navbar-toggler').css('display') !== 'none'; // eslint-disable-line
};

// Small Screen Check
EDIL.isSmallScreen = function() {
    return $(window).width() <= 1366; // ~~iPad Pro width or smaller~~
};

//
// Mobile Side Bar
// --------------------------------------------------------------------------
EDIL.mobileSidebar = function() {
    //this.$sidebar.addClass('sidebar-mobile');
    if(!$('.sidebar-drop-menu-btn').length) {
        this.$sidebar.prependTo(this.$sidebarDropMenu);
        $('<button class="sidebar-drop-menu-btn">Course Tools Menu</button>').insertBefore(this.$sidebarDropMenu);
        this.$sidebarDropMenu.addClass('posrel');
    }
    $('.masthead').css('margin-bottom', 0);

    // var $msh = $('.mobile-sidebar').find('h3');
    // $msh.off('click');
    // $msh.on('click', function() {
    //     $(this).toggleClass('collapse');
    //     $(this)
    //         .next()
    //         .toggleDisplay();
    // });
};

EDIL.sidebarDropMenu = function() {
    $('.sidebar-drop-menu-btn').on('click', function() {
        $(this).toggleClass('active');
        $(this).next('.sidebar-drop-menu').slideToggle('fast');
        $('.main-overlay').toggle();
    });
    $('.sidebar-drop-menu').width($('.content').width());
};


// //
// // Activate Mobile Menu
// // --------------------------------------------------------------------------
// EDIL.activateMobileMenu = function(icon) {
//     icon.on('click', function() {
//         EDIL.$headerNav.toggleDisplay();
//     });
// };
