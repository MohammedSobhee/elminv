var EDIL = {
    //
    // Settings
    // --------------------------------------------------------------------------
    toggleType: 'slide',
    toggleSpeed: 200,
    scrollSpeed: 500,
    isAdmin:
        $('.admin-bar').length || $('.elementor-editor-active').length || 0,

    //
    // Elements
    // --------------------------------------------------------------------------
    // Misc

    // From www.inventionlandinstitute.com
    $header: $('#header'),
    $nav: $('#nav-header>li a[href="#"]'),
    $mobileMenu: $('.mobile-menu'),
    $inputs: $('input,select,textarea'),
    $videoPlayBtn: $('.video-play'),
    // From www.inventionlandinstitute.com

    $page: $('html,body'),
    $sidebar: $('.sidebar'),
    $sidebarDropMenu: $('#sidebar-drop-menu'),
    $sidebarNav: $('.sidebar-menu', '.sidebar'),
    $content: $('#content'),
    $scrollTo: $('.scroll-to', '#content'),
    $boxVideo: $(
        '.teachertextbox iframe, .objectivestextbox iframe',
        '#content'
    ),
    $contentAndFooter: $('.footer, .footer-social, #content'),
    $footerTop: $('.footer-social'),
    $teacherVideo: $('.teachertextbox iframe, .regulartxtbox iframe'),
    $teacherTubeInTxtBox: $(
        '.challengetxtbox iframe, .regulartxtbox iframe',
        '#content'
    ),

    // Header
    $headerNav: $('.header-nav ul', '#header'),
    $schoolAdminNav: $('.school-admin-nav ul', '#header'),
    $menuIcon: $('.menu-icon', '#header'),
    $logoutLink: $('.nav-logout', '#header'),
    $supportLink: $('.nav-support', '#header'),

    // Toggle list content
    $standardsLabel: $('.section-toggle-standards p strong', '#content'),
    $standardsList: $('.section-toggle-standards ul', '#content'),
    $standardsHeader: $('.section-toggle-standards h2', '#content'),
    $toggleListLabel: $('.section-toggle-standards strong', '#content'),
    $toggleList: $('.section-toggle-standards ul', '#content'),
    $expandListTrigger: $('.expand-lists', '#content'),

    // Challenge and Regular Text Box Content
    //$regularHeader: $('.regulartxtbox h2, .challengetxtbox h2', '#content'),
    // $regularContent: $(
    //     '.regulartxtbox .slidertxtbox, .regulartxtbox ul, .regulartxtbox p, .regulartxtbox h3, .regulartxtbox div, .challengetxtbox ul, .challengetxtbox p, .challengetxtbox h3, .challengetxtbox div',
    //     '#content'
    // ),
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
    $teacherNotes: $('.acs .section-content-wrapper', '#content'),
    //$expandNotesTrigger: $('.expand-notes', '.sidebar'),

    // Forms
    $worksheetForm: $('#worksheet-form'),
    $registerForm: $('#register-form'),
    $confirmSubmit: $('.confirm-submit', 'form'),
    $selectInput: $('select[class^="select_"]', 'form'),
    $clearFormTrigger: $('.clear-form'),
    $onChangeFormSelect: $('.onchangeform select', '#content'),
    $gradeSelectField: $('h3.grade select', '#content'),
    $triggerForm: $('.triggerform'),
    $addProjectForm: $('.add-project'),

    // Worksheet list toggles
    $showListToggle: $('.show-list', '#content'),
    $listToggle: $('.toggle-list', '#content'),
    $listWorksheetStudent: $('.list-worksheet, .list-student', '#content'),
    $listProjectChildList: $('.list-project li ul', '#content'),
    $openPendingToggle: $('.open-pending', '#content'),
    $openGradedToggle: $('.open-graded', '#content'),
    $grade: $('.grade', '#content'),
    $gradeComplete: $('.grade-complete', '#content'),

    //
    //  on Ready / Init
    // --------------------------------------------------------------------------
    init: function() {
        // From www.inventionlandinstitute.com
        this.miscFix();
        this.eduSearch();
        this.scrollToSection();
        this.selectBoxURLChange();
        this.completedInputs();
        this.fixVimeo();
        // From www.inventionlandinstitute.com

        // Mobile Menu
        this.isMobile() && this.mobileSidebar();
        this.isMobile() && this.sidebarDropMenu();

        // If logged in as admin
        // this.isAdmin &&
        //     ($('.header div', '#header').removeClass('sticky-top'),
        //     $('body').css('position', 'static'),
        //     $('.footer').hide());

        // James' tabs
        // $('#tabs, #tabs2, #tabs3').tabs();
        // $('#class_name').length > 0 && $('#tabs').tabs({ active: 1 });
        // $('.imported-user').length > 0 &&
        //     $('#tabs, #tabs2').tabs({ active: 1 });

        //
        // Helpers
        // --------------------------------------------------------------------------

        // Close section popup
        this.closeSectionPopup();
        // Scroll to section
        this.scrollToSection();

        // Prevent Defaults
        this.preventDefaults();
        // Fix video issues
        this.fixVimeo();

        //
        // Stickies
        // --------------------------------------------------------------------------

        // Resize fixes
        //this.resizeFix();
        // Sticky transitions
        //this.stickyTransitions();

        //
        // Toggle Content
        // --------------------------------------------------------------------------
        this.toggleContent();

        // Activate toggle list with header
        this.toggleListContent(this.$standardsLabel, this.$standardsList);

        // Challenges / Regular text box drop down
        //this.toggleListContent('', this.$regularContent, this.$regularHeader);
        // Activate toggle list
        //this.toggleListContent(this.$toggleListLabel, this.$toggleList);
        // Notes Toggles
        this.toggleInnerContent(
            this.$teacherNotesWrapper,
            'Instructor note',
            'Instructor Notes'
        );
        // Expand All Content
        this.expandContent(this.$toggleList, this.$expandListTrigger, '');
        this.expandContent(this.$teacherNotes, 0, 'Instructor Notes');

        // Prevent default on links;
        this.deactivateLinks('.grayed-out');

        //
        // Worksheet Toggle Content
        // --------------------------------------------------------------------------

        // Teacher worksheet toggle
        // this.listParentToggle(this.$showListToggle, this.$listWorksheetStudent);
        // Open Pending Worksheets
        // this.openLists(
        //     this.$openPendingToggle,
        //     this.$grade,
        //     '.list-project',
        //     '.list-project-worksheet'
        // );
        // Open Graded Worksheets
        // this.openLists(
        //     this.$openGradedToggle,
        //     this.$gradeComplete,
        //     '.list-project',
        //     '.list-project-worksheet'
        // );

        // // Global child list toggle
        // this.globalChildListToggle(this.$listToggle);

        //
        // Worksheet Forms
        // --------------------------------------------------------------------------
        // Confirm submit
        // this.confirmSubmit();
        // // Select values to input
        // this.formSelectToInput(this.$selectInput, '.select_input');
        // // Add project form trigger
        // this.addFormTrigger(this.$triggerForm, this.$addProjectForm);
        // // Clear form
        // this.clearForm(this.$clearFormTrigger, this.$registerForm, 'Register');
        // // Register to Edit button switch
        // this.formButtonSwitch(this.$registerForm, 'input[name=uName]', 'Edit');
        // // Select auto submit
        // this.onChangeSubmit(this.$onChangeFormSelect);
        // // Teacher Grade Select Trigger
        // this.teacherGradeSelect(this.$gradeSelectField);
        // // Disable worksheets when grading;
        // this.disableWorkSheets('.worksheet-form-grade');
        // // Style completed fields
        // this.completedFields();
        // // Duplicate Submit Buttons
        // this.duplicateButtonAction(
        //     $('#submit-buttons'),
        //     'input[type="submit"]'
        // );
        // // Add upload progress to file inputs
        // this.uploadProgress();
        // // Set worksheet open status cookie
        // this.worksheetListCookie();

        // Insert videowrapper for Boxed Videos
        // this.$boxVideo.wrap(this.videoWrapper);
        // Activate mobile menu
        //this.activateMobileMenu(this.$menuIcon);
        // iframe Triggers
        //this.iFrameTriggers();
        // Footer Animation
        //!this.isMobile() && this.footerAnimation();
        // Fix Teacher Videos
        // this.videoFix();
        // move slider
        // this.moveSliderTxtBox();
        // Add image to video
        //this.addImageToVideo(this.$teacherTubeInTxtBox);
    }
};

//
// Call Scripts, Gather jQuery custom extensions
// --------------------------------------------------------------------------
(function($, window, document, app, ext) {
    // eslint-disable-line
    $(function() {
        app.init();
    });
    $.fn.extend(ext);
})(window.jQuery, window, document, EDIL, EDILJQ);
