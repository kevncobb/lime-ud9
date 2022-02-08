(function($, Drupal) {
  if ($(window).width() < 768) {
    Drupal.behaviors.colorbox = {
      attach: function(context, settings) {
        //Configure colorbox call back to resize with custom dimensions
        $.colorbox.settings.onLoad = function() {
          colorboxResize();
        }
        //Customize colorbox dimensions
        var colorboxResize = function(resize) {
          var width = "90%";
          var height = "90%";
          if ($(window).width() > 768) {
            width = "1120"
          }
          if ($(window).height() > 700) {
            height = "900"
          }
          $.colorbox.settings.height = height;
          $.colorbox.settings.width = width;
          //resize video in an iframe
          if ($('iframe')[0]) {
            $('iframe')[0].setAttribute("width", $("#colorbox").css("width").replace(/[^-\d\.]/g, '') - 40 + "px");
            $('iframe')[0].setAttribute("height", ($("#colorbox").css("width").replace(/[^-\d\.]/g, '') * 360 / 640) + 36 + "px");
          }
          //if window is resized while lightbox open
          if (resize) {
            $.colorbox.resize({
              'height': height,
              'width': width
            });
          }
        }
        //In case of window being resized
        $(window).resize(function() {
          colorboxResize(true);
        });

      }

    };
  }
  $('a.use-ajax').click(function(event) {
    var currentNode = $(document.activeElement);
    $(document).bind('cbox_closed', function(){
      setTimeout(function(){ currentNode.focus(); }, 200);
    });
  });

})(jQuery, Drupal, drupalSettings);
