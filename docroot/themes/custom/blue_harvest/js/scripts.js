/**
 * @file
 * Behaviors for the blue_harvest theme.
 */

(function($, _, Drupal) {
  Drupal.behaviors.blue_harvest = {
    attach: function() {
      // blue_harvest JavaScript behaviors goes here.

        $('.navbar .dropdown').hover(function() {
          $(this).find('.dropdown-menu').first().stop(true, true).delay(250).slideDown();

        }, function() {
          $(this).find('.dropdown-menu').first().stop(true, true).delay(100).slideUp();

        });

        $('.navbar .dropdown > a').click(function(){
          location.href = this.href;
        });

    }
  };
  Drupal.behaviors.focus_search_bar_when_opened = {
    attach: function (context, settings) {
      $(context).find('button.btn.search-button').on('click touchstart', function () {
        var focus = function () {
          $('form#search-block-form input.form-search').focus();
        };
        setTimeout(focus, 500);
      });
    }
  };
  // Youtube Modal
  Drupal.behaviors.youtube_modal = {
    attach: function (context, settings) {
      $(context).find('.mobile_video_cta_link').find('[data-toggle="modal"]').click(function() {
        //FUNCTION TO GET AND AUTO PLAY YOUTUBE VIDEO FROM DATATAG
            var theModal = $(this).data( "target" ),
              videoSRC = $(this).attr( "data-theVideo" ),
              videoSRCauto = videoSRC+"?autoplay=1" ;
            $(theModal+' iframe').attr('src', videoSRCauto);
            $(theModal+' button.close').click(function () {
              $(theModal+' iframe').attr('src', videoSRC);
            });
        $(context).find('.youtubeModal').on('hidden.bs.modal', function () {
          $(".youtubeModal iframe").attr('src', videoSRC);
        });
      });
    }
  };

})(window.jQuery, window._, window.Drupal);


