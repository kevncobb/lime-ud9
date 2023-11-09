/**
 * @file
 * Attaches behaviors for the Localization client toolbar tab.
 */

(function ($, Drupal, once, document) {

  "use strict";

  /**
   * Attaches the toolbar behavior.
   */
  Drupal.behaviors.l10n_client_ui = {
    attach: function (context) {
      $(once('l10n_client_ui', 'body')).each(function () {
        $('#toolbar-tab-l10n_client_ui').click(function () {
          if (Drupal.l10n_client_ui.buildUi()) {
            Drupal.l10n_client_ui.showModal();
          }
        });
      });

      $(once('l10n_client_ui_init', '.l10n_client_ui--container.ajax-processed .l10n-client-ui-translation-form')).each(function () {
        if (Drupal.l10n_client_ui.buildUi()) {
          Drupal.l10n_client_ui.toggle(true);
          $(window).trigger('resize.dialogResize')
        }
      });

      $(once('l10n_client_ui', '.l10n-client-ui-translation-form')).each(function () {
        let $form = $(this);
        $form.find('.form-item-language select').change(function () {
          Drupal.l10n_client_ui.displayStats();
          Drupal.l10n_client_ui.runFilters();
        });
        $form.find('.form-item-type select').change(function () {
          Drupal.l10n_client_ui.runFilters();
        });
        $form.find('.form-item-search input').keyup(function () {
          Drupal.l10n_client_ui.runFilters();
        });
      });
    }
  };

  /**
   * Save a new translation and update the interface.
   */
  Drupal.AjaxCommands.prototype.saveTranslation = function (ajax, response, status) {
    let translation = $(response.selector).closest('tr').find('textarea');
    if (translation.val().length) {
      $(response.selector).prop('disabled', true).addClass('saved');
      drupalSettings.l10n_client_ui_strings[translation.data('l10n-client-ui-langcode')][translation.data('l10n-client-ui-context')][translation.data('l10n-client-ui-source')]['translation'] = translation.val();
      Drupal.l10n_client_ui.displayStats();
      $(translation).data('l10n-client-ui-translation', translation.val());
      if (!$(translation).data('l10n-client-ui-translated')) {
        $(translation).data('l10n-client-ui-translated', true);
        $(response.selector).closest('tr').fadeOut();
      }
    }
  };

  Drupal.l10n_client_ui = Drupal.l10n_client_ui || {};

  Drupal.l10n_client_ui.toggle = function (isActive) {
    $('#toolbar-tab-l10n_client_ui button').toggleClass('active', isActive).prop('aria-pressed', isActive);
  };

  /**
   * Build the list of strings for the translation table.
   */
  Drupal.l10n_client_ui.buildUi = function () {
    let strings = drupalSettings.l10n_client_ui_strings;
    let sources = drupalSettings.l10n_client_ui_sources;

    $('.l10n_client_ui--container table :input[type="submit"]').prop('disabled', true);

    $('.l10n_client_ui--container table textarea').keyup(function () {
        let disabled = $(this).val() == '';
        $(this).closest('tr').find('td.l10n_client_ui--save :input[type="submit"]').prop('disabled', disabled).removeClass('saved');
    });

    $('.l10n_client_ui--container table td.l10n_client_ui--skip').click(function () {
        $(this).closest('tr').fadeOut();
    });

    // Initialize the interface with statistics and filter based on defaults.
    Drupal.l10n_client_ui.displayStats();
    Drupal.l10n_client_ui.runFilters();

    return true;
  };

  /**
   * Execute filters on the list of translatable strings.
   */
  Drupal.l10n_client_ui.runFilters = function () {
    let langcode = $('.l10n-client-ui-translation-form .form-item-language select').val();
    let type = $('.l10n-client-ui-translation-form .form-item-type select').val();
    let search = $('.l10n-client-ui-translation-form .form-item-search input').val();

    $.each($('.l10n_client_ui--container table tr textarea'), function (i, el) {
      let visible = false;
      if ($(el).data('l10n-client-ui-langcode') === langcode && $(el).data('l10n-client-ui-translated').toString() === type) {
        visible = true;
        if (search.length) {
          let source = $(el).data('l10n-client-ui-source');
          let translation = $(el).data('l10n-client-ui-translation');
          if ($(el).data('l10n-client-ui-translated') === "true") {
            visible = source.indexOf(search) >= 0 || translation.indexOf(search) >= 0;
          }
          else {
            visible = source.indexOf(search) >= 0;
          }
        }
      }
      $(el).closest('tr').toggle(visible);
    });
  };

  /**
   * Display stats on the form about translation progress.
   */
  Drupal.l10n_client_ui.displayStats = function () {
    let stats = Drupal.l10n_client_ui.computeStats();
    let percent = Math.round((stats.translated / stats.all) * 100);
    $('.l10n-client-ui-translation-form .form-item-stats label').text(Drupal.t('@percent% translated', {'@percent': percent}));
    $('.l10n_client_ui--stats-done').css('width', 2 * percent);
  };

  /**
   * Compute translation status for the currently selected language.
   */
  Drupal.l10n_client_ui.computeStats = function () {
    let langcode = $('.l10n-client-ui-translation-form .form-item-language select').val();
    let allCount = 0;
    let translatedCount = 0;
    let strings = drupalSettings.l10n_client_ui_strings;
    for (let context in strings[langcode]) {
      for (let string in strings[langcode][context]) {
        if (strings[langcode][context][string]['translation'] !== false) {
          translatedCount++;
        }
        allCount++;
      }
    }
    return {'all': allCount, 'translated': translatedCount};
  };

  Drupal.l10n_client_ui.showModal = function () {
    let modal = Drupal.dialog(
        $('.l10n_client_ui--container').get(0),
        {
          title: Drupal.t('Translation interface'),
          buttons: [
            {
              text: Drupal.t('Close'),
              click: function () {
                $(this).dialog("close");
                Drupal.l10n_client_ui.toggle(false);
              }
            }
          ],
          width: '66%',
          close: function () {
            Drupal.l10n_client_ui.toggle(false);
          }
        }
    );
    $('.l10n_client_ui--container:not(.ajax-processed) .rebuild').trigger('mousedown');
    $('.l10n_client_ui--container:not(.ajax-processed)').addClass('ajax-processed');
    modal.showModal();
  };

})(jQuery, Drupal, once, document);
