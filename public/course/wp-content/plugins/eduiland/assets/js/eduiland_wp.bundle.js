!function(e,t){e(function(){n.init()});var n={toggleType:"slide",toggleSpeed:200,scrollSpeed:500,isAdmin:e(".admin-bar").length||e(".elementor-editor-active").length||0,$inputs:e("input,select,textarea"),$page:e("html,body"),$sidebar:e("#sidebar"),$sidebarCourseware:e("#sidebar-courseware"),$footer:e(".footer"),$scrollTo:e(".scroll-to","#content"),$navbar:e("#navbarMenu"),$standardsLabel:e(".section-toggle-standards p strong","#content"),$standardsList:e(".section-toggle-standards .elementor-shortcode > ul, .section-toggle-standards .elementor-shortcode > ol","#content"),$standardsHeader:e(".section-toggle-standards h2","#content"),$toggleListLabel:e(".section-toggle-standards strong","#content"),$toggleList:e(".section-toggle-standards ul, .section-toggle-standards ol","#content"),$expandListTrigger:e(".expand-lists","#content"),$regularHeader:e(".section-note .elementor-widget:first-of-type, .section-action .elementor-widget:first-of-type","#content"),$regularContent:e(".section-action .elementor-widget:not(:first-of-type), .section-note .elementor-widget:not(:first-of-type)","#content"),$teacherNotesWrapper:e(".acs"),$teacherNotes:e(".acs .section-content-wrapper, .acs-inline-toggle .elementor-widget-container","#content"),init:function(){this.quickMisc(),this.scrollToSection(),this.selectBoxURLChange(),this.selectSubmit(),this.completedInputs(),this.preventDefaults(),this.isMobile()&&this.mobileSidebar(),this.eduSearch(),this.toggleContent(),this.toggleListContent(this.$standardsLabel,this.$standardsList),this.toggleInnerContent(this.$teacherNotesWrapper,"Instructor note","Instructor Notes"),this.expandContent(this.$toggleList,this.$expandListTrigger,""),this.expandContent(this.$teacherNotes,0,"Instructor Notes"),this.worksheetFindModal(),this.fixVideos(),this.acsObjectivesStandards(),this.demoDisableLinks(),this.posters(),this.resources(),this.markReadAnnouncement()},quickMisc:function(){e('[data-toggle="popover"]').popover({html:!0}),e('[data-toggle="tooltip"]').tooltip(),e("p").each(function(){var t=e(this);0==t.html().replace(/\s|&nbsp;/g,"").length&&t.remove()}),this.isAdmin&&e(".section-toggle-header").length>0&&e(".section-toggle-header").addClass("highlight"),e(".acs-inline.elementor-widget-text-editor","#content").prepend('<div class="label">Instructor note</div>'),"student"===eduUser.role&&e(".acs, .acs-inline, .acs-button").remove(),t.FontAwesomeConfig={searchPseudoElements:!0}},selectBoxURLChange:function(){e(".select-url-change").on("change",function(){e("form").find(".btn-action").prop("disabled",!0);var n=e(this).val();t.location.href=n})},selectSubmit:function(){e(".select-submit").on("change",function(){e(this).submit()})},completedInputs:function(){this.$inputs.on("blur",function(){e(this).each(function(){e(this).val()&&e(this).addClass("completed")})})},eduSearch:function(){var t=e("input[type=search]"),s=e(".search-clear"),i=e(".toggle-search"),o=e(".header-search");this.isSmallScreen()&&t.length&&t.val().length>0&&s.show(),t.on("keydown change focus",function(){var t=e(this).siblings(".search-clear");e(this).val().length>0&&t.show(),e(this).val().length<2&&t.hide()}),s.on("click",function(){t.val("")});var a=i.outerWidth();e(".toggle-search a").on("click",function(){if(o.hasClass("search-show"))o.animate({width:"5px"},{complete:function(){n.isMobile()&&o.hide().removeClass("search-show")}}),!n.isMobile()&&i.animate({width:a},{complete:function(){o.hide().removeClass("search-show"),i.removeClass("focus")}});else{const e=n.isMobile()?"69%":"160px";i.addClass("focus"),o.show(),o.find("input[type=search]").focus(),o.animate({width:e}),!n.isMobile()&&i.animate({width:"265px"}),o.addClass("search-show")}})},stickyTransitions:function(){if(!this.isAdmin&&!this.isMobile()){if(this.$sidebar.length){var s=e(".header, .masthead"),i=e("#content"),o=e(".sidebar-wrapper"),a=e("#sidebar-hidden-menu-btn"),r=e(".sidebar-menu-admin"),l=e(".masthead").height()+s.height(),c=e("#sidebar-admin");a.on("click",function(){r.slideToggle()})}e(t).on("scroll",function(){var t=e(".sidebar-wrapper.sticky-transition"),s=n.$sidebar.width();n.$sidebar.length&&e(this).scrollTop()>l&&i.height()>n.$sidebar.height()?(o.addClass("sticky-transition"),c.length&&r.slideUp(),c.length&&a.slideDown(),n.$sidebar.not(".sidebar-scroll").css("width",s)):t.length&&(o.removeClass("sticky-transition"),c.length&&r.slideDown(),c.length&&a.slideUp(),n.$sidebar.attr("style",""))})}},scrollToSection:function(){var s=this.scrollSpeed;this.$scrollTo.on("click",function(){e("html, body").animate({scrollTop:e(this.hash).offset().top},s)}),e(".arrow-down").on("click",function(){e("html, body").animate({scrollTop:e("div[data-elementor-type=post]").offset().top},n.scrollSpeed)}),e(t).on("elementor/frontend/init",function(){elementorFrontend.on("components:init",function(){elementorFrontend.utils.anchors.setSettings("scrollDuration",0)}),elementorFrontend.hooks.addFilter("frontend/handlers/menu_anchor/scroll_top_distance",function(e){return e-50})})},preventDefaults:function(){e('a[href^="#"]').on("click",function(e){e.preventDefault()})},isMobile:function(){return"none"!==e(".navbar-toggler").css("display")},isSmallScreen:function(){return e(t).width()<=768},mobileSidebar:function(){e(".dropdown-submenu a").on("click",function(t){e(this).next("ul").toggle(),t.stopPropagation(),t.preventDefault()});var t=this.$sidebar.find("#sidebar-header-courseware a").text(),n=this.$navbar.find("li a:contains("+t+")"),s=n.parent(),i=this.$sidebar.find("#sidebar-assignments");i.prependTo("#content"),i.find("li a").each(function(){e(this).html("Additional Assignment: "+e(this).text())}),this.$sidebar.find("ul li a").addClass("nav-link"),t.length>0&&(this.$sidebar.find("#sidebar-courseware").prepend('<li class="menu-item dropdown-menu-item"><strong><a class="nav-link" href="'+n.attr("href")+'">'+t+"</a></strong></li>"),n.html(t+" <i>(Currently viewing)</i>"),n.attr("href","#"),s.find("a"),s.addClass("dropdown"),this.$sidebar.find("#sidebar-courseware li"),this.$sidebar.find("#sidebar-courseware").attr("aria-labelledby","currentSelection").addClass("dropdown-submenu").removeClass("sidebar-menu").appendTo(s)),this.$sidebar.remove()},toggleContent:function(){var t=e(".section-toggle");t.find(".section-toggle-header").each(function(){var s=e(this).closest(".section-toggle").find(".section-toggle-content");e(this).on("click",function(i){e(this).toggleClass("highlight"),s.toggleClass("open"),s.slideToggle({duration:n.toggleSpeed,progress:function(){n.$page.on("scroll mousedown wheel DOMMouseScroll mousewheel keyup touchmove",function(){n.$page.stop()}),n.$page.animate({scrollTop:e(i.target).offset().top-20},50)}}),t.find(".section-toggle-content").not(s).each(function(){e(this).is(":visible")&&(e(this).removeClass("open").hide(),e(this).prev(".section-toggle-header").removeClass("highlight"))})})})},toggleListContent:function(t,n){var s=this.toggleSpeed;t.on("click",function(){var i=e(this).parent().next();t.removeClass("highlight"),e(this).toggleClass("highlight"),"block"==i.css("display")&&e(this).removeClass("highlight"),n.not(i).slideUp(s),i.slideToggle(s)})},toggleInnerContent:function(t,s,i){if(this.isAdmin&&(t.find(".section-content-wrapper").show(),t.addClass("open")),t.prepend('<div class="label">'+s+"</div>"),t.length>0&&"student"!==eduUser.role){var o=`<button type="button" class="btn btn-primary btn-block btn-sm expand-content">Expand all ${i}</button>`;this.isMobile()?(this.$navbar.prepend(o),e(".navbar .expand-content").on("click",function(){e(".navbar-collapse").collapse("hide")})):this.$sidebarCourseware.after(o)}t.on("click",function(){e(this).toggleClass("open"),e(this).hasClass("acs-inline-toggle")?e(this).find(".elementor-widget-container").slideToggle(n.toggleSpeed):e(this).find(".section-content-wrapper").slideToggle(n.toggleSpeed)})},expandContent:function(t,s,i){var o=s.length>0?s:e(".expand-content"),a=n.toggleSpeed,r=i.length>0?i:"";o.on("click",function(n){n.preventDefault();var s=t.prev().find("strong");t.filter(":visible").length>0?(t.parents(".elementor-top-section").removeClass("open"),e(this).html("Expand all "+r).removeClass("collapsed"),t.slideUp(a),s.length>0&&s.each(function(){e(this).removeClass("highlight")})):(t.parents(".elementor-top-section").addClass("open"),t.parents().addClass("open"),e(this).html("Collapse all "+r).addClass("collapsed"),t.slideDown(a),s.length>0&&s.each(function(){e(this).addClass("highlight")}))})},worksheetFindModal:function(){e(".find-worksheet").on("click",function(n){n.preventDefault();var s=e("#worksheetFindModal"),i=s.find("iframe"),o=s.find(".modal-title"),a=s.find(".modal-header");a.addClass("border-0"),o.text("Retrieving..."),s.modal("toggle");var r=this.href,l=r.replace("find","")+"pcount";e.get(l,function(e){1===e.project_count?(s.modal("hide"),t.top.location.href="/worksheets/"+e.worksheet_id+"/"+e.projects[0].id):(a.removeClass("border-0"),o.text(0===e.project_count?"Add a project to begin:":"Select a project for the worksheet:"),s.find("iframe").attr("src",r),i.fadeIn(),s.find(".modal-status").fadeOut())})})},acsObjectivesStandards:function(){1===eduUser.settings.objectives&&e(".section-objectives").removeClass("acst"),1===eduUser.settings.standards&&e(".section-toggle-standards").removeClass("acst")},fixVideos:function(){var t=e('iframe[src*="https://player.vimeo.com"].video');t.length>0&&t.each(function(){e(this).parent().hasClass("video-frame")&&e(this).hasClass("elementor-video-iframe")&&e(this).wrap('<div class="video-frame"></div>')})},posters:function(){var t=function(){var t=e(this),n=t.data("flip");t.data("flip",t.attr("src")),t.attr("src",n)};e(function(){e(".poster[data-flip]").each(function(){var t=e(this);(new Image).src=t.data("flip");const n=t.data("lightbox");if(n.length){const e=t.attr("alt");let s=btoa(`{"url":"${n}","type":"image","title":"${e}"}`);t.wrap(`<a href="#elementor-action%3Aaction%3Dlightbox%26settings%3D${s}">`)}}).hover(t,t)})},demoDisableLinks:function(){"93"===eduUser.school_id&&e("body:not(.parent-pageid-18):not(.parent-pageid-961):not(.page-id-961):not(.page-id-18):not(.page-id-934):not(.page-id-16)").length&&e("#content a").each(function(){var t=e(this).attr("href");/^((?!.*step-1|.*25\/find|.*a-setting|.*number-1|.*4-script-storming|.*character-traits|.*7-success-qualities|.*9-step-method(?![/]?[a-z]+)).+)/g.test(t)&&(e(this).removeAttr("href"),e(this).attr("data-toggle","tooltip"),e(this).attr("title","Disabled for demo"),e(this).tooltip())})},resources:function(){eduUser.class_type>2?(e("#resource-chipper").hide(),e("#resource-bugreport").show()):(e("#resource-bugreport").hide(),e("#resource-chipper").show())},deactivateLinks:function(t){e(t+", "+t+" a").on("click",function(e){e.preventDefault()}),e(t+" a").removeClass("loadbox-norefresh cboxElement")},markReadAnnouncement:function(){e(".announcements").on("mouseover",function(){var t=e(this).find(".announcements-title");setTimeout(function(){t.removeClass("announcements-unread"),t.find(".announcements-unread-icon").fadeOut("fast")},2e3)})}}}(window.jQuery,window);