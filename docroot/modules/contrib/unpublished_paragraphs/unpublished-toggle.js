(function ($, Drupal) {
  Drupal.behaviors.publishToggle = {
    attach: function (context) {
      if ($('.paragraph.unpublished', context).length > 0) {
        $('body').once('unpublished-toggle').append('<div class="unpublished-toggle">' + Drupal.t('Toggle visibility of unpublished items') + '</div>');
      }
      $('.unpublished-toggle').once('unpublished-toggle').each(function () {
        $(this).mousedown(function () {
          $('.paragraph.unpublished').toggle();
        });
      });
    }
  };
}(jQuery, Drupal));
