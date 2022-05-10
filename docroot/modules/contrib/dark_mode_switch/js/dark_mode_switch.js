(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.dark_mode_switch= {
    attach: function (context, settings) {
      // be sure this is executed only once, on page load
      if (context === document) {
        // Check Dark Mode Switch Cookie.
        const darkClass = drupalSettings.dark_mode_switch.dark_class;
        const parentElement = drupalSettings.dark_mode_switch.parent_element;
        $(context).find('#dark-mode-switch').once('dark_mode_switch').each(function () {
          var darkmodestate = window.matchMedia('(prefers-color-scheme: dark)').matches;
          if ('theme' in localStorage) {
            darkmodestate = (localStorage.theme === 'false') ? false : true;
          }
          localStorage.theme = darkmodestate;
          $(parentElement).toggleClass(darkClass, darkmodestate);
          $('#dark-mode-switch').prop("checked", darkmodestate);
        });
        // Toggle Dark Mode Switch.
        $('#dark-mode-switch').change(function () {
          $(parentElement).toggleClass(darkClass);
          localStorage.theme = $(this).prop("checked");
        });
      }
    }
  };
})(jQuery, Drupal, drupalSettings );
