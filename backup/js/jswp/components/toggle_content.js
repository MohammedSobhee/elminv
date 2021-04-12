/* eslint-disable indent */

//
// Toggle Content
// --------------------------------------------------------------------------

EDIL.toggleContent = function() {
    var $toggleClass = $('.section-toggle');
    $toggleClass.find('.section-toggle-header').each(function() {
        //var $content = $(this).siblings('.section-toggle-content'),
        var $content = $(this).closest('.section-toggle').find('.section-toggle-content')

        $(this).on('click', function(e) {
            $(this).toggleClass('highlight'); //.removeClass('unread');
            $content.toggleClass('open');

            // Move scroll to e.target and toggle
            $content.slideToggle({
                duration: EDIL.toggleSpeed,
                progress: function() {
                    //var scTop = EDIL.isMobile() ? 20 : 50;
                    var scTop = 20;
                    EDIL.$page.on('scroll mousedown wheel DOMMouseScroll mousewheel keyup touchmove', function(){
                        EDIL.$page.stop();
                    });
                    EDIL.$page.animate({
                        scrollTop: $(e.target).offset().top - scTop
                    }, 50);
                }
            });

            $toggleClass.find('.section-toggle-content').not($content).each(function() {
                if($(this).is(':visible')) {
                    //$(this).removeClass('open').toggleDisplay('hide');
                    $(this).removeClass('open').hide();
                    //$(this).removeClass('open').slideUp(EDIL.toggleSpeed);
                    $(this).prev('.section-toggle-header').removeClass('highlight');
                }
            });

        });
    });
};

// EDIL.toggleContent = function() {
//     var $toggleClass = $('.section-toggle');
//     $toggleClass.find('.section-toggle-header').on('click', function() {
//         var $content = $(this).siblings('.section-toggle-content'),
//             $that = $(this);

//         $(this).toggleClass('highlight'); //.removeClass('unread');
//         //$content.toggleClass('open').toggleDisplay();
//         $content.toggleClass('open');

//         $toggleClass.find('.section-toggle-content').not($content).each(function() {
//             if($(this).is(':visible')) {
//                 //$(this).removeClass('open').toggleDisplay('hide');
//                 $(this).removeClass('open').hide();
//                 //$(this).removeClass('open').slideUp(EDIL.toggleSpeed);
//                 $(this).prev('.section-toggle-header').removeClass('highlight');
//             }
//         });

//         // Move scroll to e.target and toggle
//         $content.slideToggle({
//             duration: 100,
//             done: function() {
//                 var scTop = EDIL.isMobile() ? 20 : 50;
//                 EDIL.$page.on('scroll mousedown wheel DOMMouseScroll mousewheel keyup touchmove', function(){
//                     EDIL.$page.stop();
//                 });
//                 EDIL.$page.animate({
//                     scrollTop: $that.offset().top - scTop
//                 },EDIL.toggleSpeed);
//             }
//         });
//     });
// };

//
// Toggle List Content
// --------------------------------------------------------------------------
EDIL.toggleListContent = function (label, content) {
    label.on('click', function() {
        var $nextUL = $(this).parent().next('ul');
        label.removeClass('highlight');
        $(this).toggleClass('highlight');
        $nextUL.css('display') == 'block' && $(this).removeClass('highlight');
        content.not($nextUL).toggleDisplay('hide');
        $nextUL.toggleDisplay();
    });
};

//
// Add toggled content inside a wrapper (Teacher Notes)
// ------------------------------------------------------------------------
EDIL.toggleInnerContent = function(wrapper, label, shortlabel) {
    if(this.isAdmin) {
        wrapper.find('.section-content-wrapper').show();
        wrapper.addClass('open');
    }

    wrapper.prepend('<div class="label">' + label + '</div>');
    wrapper.length > 0 &&
        this.$sidebar.append(
            '<a class="btn btn-primary btn-block btn-sm expand-content" href="#">Expand all ' +
                shortlabel +
            '</a>'
        );

    wrapper.on('click', function() {
        //$(this).hasClass('open') ? $(this).removeClass('open') : $(this).addClass('open');
        $(this).toggleClass('open');
        $(this).find('.section-content-wrapper').toggleDisplay();
    });
};

//
// Expand all hidden content on page (Careers)
// --------------------------------------------------------------------------
EDIL.expandContent = function(content, tgl, tp) {
    // defaults
    var toggle = tgl.length > 0 ? tgl : $('.expand-content');
    var type = tp.length > 0 ? tp : '';

    toggle.on('click', function(e) {
        e.preventDefault();
        var label = content.prev().find('strong');
        // If expanded, collapse
        if (content.filter(':visible').length > 0) {
            content.parents('.elementor-top-section').removeClass('open');
            $(this).html('Expand all ' + type).removeClass('collapsed');
            content.toggleDisplay('hide');
            if (label.length > 0) {
                label.each(function() {
                    $(this).removeClass('highlight');
                });
            }
        // If collapsed, expand
        } else {

            content.parents('.elementor-top-section').addClass('open');
            $(this).html('Collapse all ' + type).addClass('collapsed');
            content.toggleDisplay('show');
            if (label.length > 0) {
                label.each(function() {
                    $(this).addClass('highlight');
                });
            }
        }
    });
};

// //
// // Teacher worksheet toggle
// // --------------------------------------------------------------------------
// EDIL.listParentToggle = function(toggle, lists) {
//     toggle.on('click', function() {
//         lists.not($(this).next()).each(function() {
//             $(this).toggleDisplay('hide');
//             EDIL.removeNotices();
//         });
//         var className = $(this)
//             .attr('class')
//             .split(' ')[1];
//         $('.' + className)
//             .next()
//             .toggleDisplay();
//     });
// };

// //
// // Open All Submitted Worksheets
// // --------------------------------------------------------------------------
// EDIL.openLists = function(action, target, list1, list2) {
//     action.on('click', function() {
//         var $that = $(this),
//             $t = $that
//                 .closest('ul')
//                 .children()
//                 .find(target),
//             $ls1,
//             $ls2,
//             $ls;
//         // if action is toggled, close on next click
//         if ($that.hasClass('toggled')) {
//             $t.each(function() {
//                 $ls1 = $(this).parents(list1);
//                 $ls2 = $(this).parents(list2);
//                 $ls = $ls1.add($ls2);
//                 $ls1.toggleDisplay('hide');
//                 $ls2.toggleDisplay('hide', 'def');
//                 $ls.parent('li').removeClass('open');
//                 $that.removeClass('toggled');
//             });
//         } else {
//             // Close previously targetted
//             var $tog = $that.siblings('.toggled');
//             if ($tog.length) {
//                 $ls = $(list1).add(list2);
//                 $ls.toggleDisplay('hide', 'def');
//                 $tog.parents('li')
//                     .find('.open')
//                     .removeClass('open');
//                 $tog.removeClass('toggled');
//             }
//             // If targetted has requested items, open
//             if ($t.length) {
//                 var $lst1, $lst2, $lst;
//                 $t.each(function() {
//                     $lst1 = $(this).parents(list1);
//                     $lst2 = $(this).parents(list2);
//                     $lst = $lst1.add($lst2);
//                     $lst1.toggleDisplay('show', 'def');
//                     $lst2.toggleDisplay('show');
//                     $lst.parent('li').addClass('open');
//                     $that.addClass('toggled');
//                 });
//                 // Else add no results notice
//             } else {
//                 if (
//                     !$(this)
//                         .parent()
//                         .next('.success-notice').length
//                 ) {
//                     $(EDIL.showNotice('No results'))
//                         .hide()
//                         .insertAfter($(this).parent())
//                         .fadeIn('slow');
//                 } else {
//                     $(this)
//                         .parent()
//                         .next('.success-notice')
//                         .fadeIn('slow');
//                 }
//             }
//         }
//     });
// };

// //
// // Global child UL toggle
// // --------------------------------------------------------------------------
// EDIL.globalChildListToggle = function(toggle) {
//     toggle.on('click', function() {
//         $(this)
//             .next('ul')
//             .toggleDisplay();
//         $(this)
//             .parent()
//             .toggleClass('open');
//     });
// };

// //
// // Highlight Open List - wtf
// // --------------------------------------------------------------------------
// EDIL.highlightOpenList = function(list) {
//     list.prev().toggleClass('highlight', list.length && list.is(':visible'));
// };

//
// prevent default on grayed out links
// --------------------------------------------------------------------------
EDIL.deactivateLinks = function(link) {
    $(link + ', ' + link + ' a').on('click', function(e) {
        e.preventDefault();
    });
    $(link + ' a').removeClass('loadbox-norefresh cboxElement');
};
