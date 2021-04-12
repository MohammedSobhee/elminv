//
// Fix various youtube issues
// --------------------------------------------------------------------------
EDIL.fixVimeo = function() {
    // Vimeo / Mashable
    var $vimVideos = $('.video iframe[src*="https://player.vimeo.com"], .video iframe[src*="https://mashable.com"]'),
        $vEl = $('.video');

    if($vimVideos.length > 0 && $vEl.length > 0) {
        $vimVideos.each(function () {
            videoFrame($(this));
            $(this).attr('data-aspectRatio', this.height / this.width)
                .removeAttr('height')
                .removeAttr('width');
        });

        $(window).resize(function() {
            var newWidth = $vEl.width();
            $vimVideos.each(function() {
                var $el = $(this);
                $el.width(newWidth).height(newWidth * $el.attr('data-aspectRatio'));
            });
        }).resize();
    }

    function videoFrame(video) {
        if (!video.parent().hasClass('video-frame')) {
            video.wrap('<div class="video-frame"></div>');
        }
    }
};


//
// Fix Vimeo Issue
// --------------------------------------------------------------------------
// EDIL.fixVimeo = function() {
//     var $tt = $('iframe[src^="https://player.vimeo.com"]');
//     var url;
//     $tt.each(function() {
//         $(this).attr('allowfullscreen', '');
//         $(this).attr('mozallowfullscreen', '');
//         $(this).attr('webkitallowfullscreen', '');
//         url = $(this).attr('src') || 'none'; // Remove this line and the one below it
//         $(this).attr('src', url); // eventually.
//     });
// };

//
// Teacher Video Fix
// --------------------------------------------------------------------------
// EDIL.videoFix = function() {
//     EDIL.$teacherVideo.parent().addClass('youtubeBlock');
//     $('iframe').attr('allowfullscreen', '');
// };

//
// Use image for videos
// --------------------------------------------------------------------------
// EDIL.addImageToVideo = function(iframe) {
//     iframe.each(function() {
//         // find iframe's video image thumb
//         var $img = $(this)
//             .parent()
//             .next()
//             .children('img');

//         if ($img.length > 0) {
//             // add class off to iframe parent
//             $(this)
//                 .parent()
//                 .addClass('off');

//             // wrap all video images with div icon
//             $img.wrap('<div class="video-icon"></div>');
//             // On click image, set autoplay, hide image's p, remove off class
//             $img.on('click', function() {
//                 //var $vid = $(this).parent().parent().prev().children('iframe');
//                 //$vid.attr('src', $vid.attr('src') + '&autoplay=true'); // doesn't work when flash turned off
//                 $(this)
//                     .parents('p')
//                     .hide();
//                 $(this)
//                     .parent()
//                     .parent()
//                     .prev()
//                     .show()
//                     .removeClass('off');
//             });
//             // On click video icon, set auto play, image's p, remove off class
//             $('.video-icon').on('click', function() {
//                 //var $vid = $(this).parent().prev().children('iframe');
//                 //$vid.attr('src', $vid.attr('src') + '&autoplay=true'); // doesn't work when flash turned off
//                 $(this)
//                     .parents('p')
//                     .hide();
//                 $(this)
//                     .parent()
//                     .prev()
//                     .show()
//                     .removeClass('off');
//             });
//         }
//     });
// };
