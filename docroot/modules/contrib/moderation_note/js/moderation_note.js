/**
 * @file
 * Contains all Moderation Note behaviors.
 */

(function scope(Drupal, $, once) {
  // Local variable to track when the view tooltip fades.
  let viewTooltipTimeout;

  /**
   * Initializes the tooltip used to add new notes.
   *
   * @return {Object}
   *   The tooltip.
   */
  function initializeAddTooltip() {
    const $tooltip = $(
      `<a class="moderation-note-tooltip use-ajax" href="javascript;" data-dialog-type="dialog" data-dialog-renderer="off_canvas">${Drupal.t(
        'Add note',
      )}</a>`,
    ).hide();

    $('body').append($tooltip);

    return $tooltip;
  }

  /**
   * Wraps a given range in a <span> tag with the provided classes.
   *
   * @param {Range} range
   *   The given range.
   * @param {String} classes
   *   Classes you want to add to the highlight, separated by a space.
   * @return {Object}
   *   The jQuery object for the wrap (could contain multiple elements).
   */
  function highliteRange(range, classes) {
    const selection = window.getSelection();

    document.designMode = 'on';
    const { spellcheck } = document.body;
    document.body.spellcheck = false;
    selection.removeAllRanges();
    selection.addRange(range);
    document.execCommand('hilitecolor', false, 'yellow');
    document.designMode = 'off';
    document.body.spellcheck = spellcheck;

    const wrapRange = selection.getRangeAt(0);
    const $wrap = $(wrapRange.startContainer.parentNode).add(
      wrapRange.endContainer.parentNode,
    );
    // This is not a new span element.
    if ($wrap[0].attributes.length > 1) {
      $wrap.addClass('moderation-note-highlight-existing');
    }
    $wrap.removeAttr('style').addClass(classes);
    selection.collapseToEnd();

    return $wrap;
  }

  /**
   * Finds the offset of a range relative to a given parent element.
   *
   * Modified from http://stackoverflow.com/a/11358084, written by benjamin-rögner.
   *
   * @param {Node} element
   *   The element to compare against. Defaults to body.
   * @param {Range} range
   *   The range that requires comparison.
   * @return {Number}
   *   The offset of the range.
   */
  function getCursorPositionInTextOf(element, range) {
    element = element || document.body;
    const parentRange = document.createRange();
    parentRange.setStart(element, 0);
    parentRange.setEnd(range.startContainer, range.startOffset);
    // Measure the length of the text from the start of the given element to
    // the start of the current range (position of the cursor).
    return parentRange.cloneContents().textContent.length;
  }

  /**
   * Performs a text search within the page based on a given string.
   *
   * Modified from http://stackoverflow.com/a/5887719, written by @tpdown.
   *
   * @param {String} text
   *   The string to search for. Should not contain HTML.
   * @param {Node} element
   *   The parent element to perform the search within. Defaults to body.
   * @param {Number} offset
   *   The text offset from the start of the element to start the search.
   * @param {String} id
   *   The unique ID of this search. Used to track fuzzy matches.
   * @return {Range}
   *   The Range object of the successful search.
   */
  function doSearch(text, element, offset, id) {
    const scroll = $(window).scrollTop();
    element = element || document.body;
    offset = offset || 0;
    let match;
    let selection;
    let range;
    let currentOffset;
    let currentDifference;

    if (window.find && window.getSelection) {
      text = text.replace('\r\n', '\n');

      selection = window.getSelection();
      selection.collapse(element, 0);

      let offsetDifference = element.innerHTML.length;
      while (window.find(text) && selection.rangeCount) {
        range = selection.getRangeAt(0);
        const $ancestor = $(range.commonAncestorContainer);
        if ($ancestor.closest(element).length) {
          currentOffset = getCursorPositionInTextOf(element, range);
          currentDifference = Math.abs(currentOffset - offset);
          if (currentDifference < offsetDifference) {
            offsetDifference = currentDifference;
            match = range;
          }
        } else {
          break;
        }
        selection.collapseToEnd();
      }

      // If the match can't be found, select a similar text range.
      if (!match) {
        selection.collapse(element, 0);
        const fuzzy = element.textContent.substr(offset, text.length);
        if (window.find(fuzzy)) {
          match = selection.getRangeAt(0);
          Drupal.moderation_note.fuzzy_matches.push(id);
        }
      }
    }

    if (selection.rangeCount) {
      selection.collapseToEnd();
    }
    $(window).scrollTop(scroll);
    return match;
  }

  /**
   * Removes the highlight and note data for a given wrapper element.
   *
   * @param {Object} $wrap
   *   The jQuery object for the wrap (could contain multiple elements).
   */
  function removeHighlight($wrap) {
    if ($wrap.is('.moderation-note-highlight-existing')) {
      $wrap.removeClass(
        'moderation-note-contextual-highlight moderation-note-highlight moderation-note-highlight-existing existing new',
      );
      $wrap.removeAttr('data-moderation-note-highlight-id');
      $wrap.removeData('moderation-note-highlight-id');
      $wrap.removeData('moderation-note');
      $wrap.off('mouseover.moderation_note');
      $wrap.off('mouseleave.moderation_note');
    } else {
      $wrap.contents().unwrap();
    }
  }

  /**
   * Removes all contextual highlights from the page.
   */
  function removeContextHighlights() {
    $('.moderation-note-contextual-highlight').each(
      function removeEachContextualHighlight() {
        if ($(this).data('moderation-note-highlight-id')) {
          $(this).removeClass('moderation-note-contextual-highlight existing');
        } else {
          removeHighlight($(this));
        }
      },
    );
  }

  /**
   * Highlights focused text while the sidebar is open.
   *
   * @param {Object} note
   *   An objects representing a Moderation Note.
   */
  function showContextHighlight(note) {
    // Remove all existing context highlights.
    removeContextHighlights();

    // If this note is already highlighted, simply add a class.
    if (note.id) {
      const $note = $(`[data-moderation-note-highlight-id="${note.id}"]`);
      if ($note.length) {
        $note.addClass('moderation-note-contextual-highlight existing');
      }
    }
    // Otherwise, we need to create a new highlight.
    else {
      const $field = $(`[data-moderation-note-field-id="${note.field_id}"]`);
      const match = doSearch(note.quote, $field[0], note.quote_offset, note.id);
      if (match) {
        highliteRange(match, 'moderation-note-contextual-highlight new');
      }
    }
  }

  /**
   * Initializes the tooltip used to view existing notes.
   *
   * @return {Object}
   *   The tooltip.
   */
  function initializeViewTooltip() {
    const $tooltip = $(
      `<a class="moderation-note-tooltip use-ajax" href="javascript;"  data-dialog-type="dialog" data-dialog-renderer="off_canvas">${Drupal.t(
        'View note',
      )}</a>`,
    ).hide();

    $('body').append($tooltip);

    // Click callback.
    $tooltip.on('click', function onToolTipClick() {
      $tooltip.hide();
      showContextHighlight($tooltip.data('moderation-note'));
    });

    $tooltip.on('mouseleave', function onToolTipMouseLeave() {
      clearTimeout(viewTooltipTimeout);
      viewTooltipTimeout = setTimeout(function toolTipMouseLeaveSetTimeout() {
        $tooltip.fadeOut('fast');
      }, 500);
    });

    $tooltip.on('mousemove', function onToolTipMouseOver() {
      $tooltip.finish().fadeIn();
      clearTimeout(viewTooltipTimeout);
    });

    return $tooltip;
  }

  Drupal.moderation_note = Drupal.moderation_note || {
    selection: {
      quote: false,
      quote_offset: false,
      field_id: false,
    },
    notes: [],
    add_tooltip: initializeAddTooltip(),
    view_tooltip: initializeViewTooltip(),
    fuzzy_matches: [],
  };

  /**
   * Command to remove a Moderation Note.
   *
   * @param {Drupal.Ajax} [ajax]
   *   The ajax object.
   * @param {Object} response
   *   Object holding the server response.
   * @param {String} response.id
   *   The ID for the moderation note.
   */
  Drupal.AjaxCommands.prototype.remove_moderation_note =
    function removeModerationNote(ajax, response) {
      const { id } = response;
      const $wrap = $(`[data-moderation-note-highlight-id="${id}"]`);
      if (Drupal.moderation_note.notes[response.id]) {
        delete Drupal.moderation_note.notes[response.id];
      }
      removeHighlight($wrap);
    };

  /**
   * Changes the URL for an ajaxified element.
   *
   * @param {Object} $element
   *   The ajaxified element you need to change the url for.
   * @param {string} url
   *   The new url, without query params.
   */
  function changeAjaxUrl($element, url) {
    Object.values(Drupal.ajax.instances).forEach((instance) => {
      if (instance && $element.is(instance.element)) {
        instance.options.url = instance.options.url.replace(/.*\?/, `${url}?`);
      }
    });
  }

  /**
   * Displays the tooltip at a position relative to the given element.
   *
   * @param {Object} $tooltip
   *   The tooltip.
   * @param {Object} $element
   *   The element to display to tooltip on.
   */
  function showViewTooltip($tooltip, $element) {
    const widthOffset = $element.outerWidth() / 2 - $tooltip.outerWidth() / 2;
    const offset = $element.offset();
    $tooltip.css('left', offset.left + widthOffset);
    $tooltip.css('top', offset.top - ($tooltip.outerHeight() + 5));

    const id = $element.data('moderation-note-highlight-id');
    const url = Drupal.formatString(Drupal.url('moderation-note/!id'), {
      '!id': id,
    });
    $tooltip.attr('href', url);
    changeAjaxUrl($tooltip, url);
    $tooltip.data('moderation-note', $element.data('moderation-note'));

    $tooltip.fadeIn('fast');
  }

  /**
   * Shows the given moderation note as a highlighted range.
   *
   * @param {Object} note
   *   An objects representing a Moderation Note.
   */
  function showModerationNote(note) {
    // Remove all existing context highlights.
    removeContextHighlights();

    const $field = $(`[data-moderation-note-field-id="${note.field_id}"]`);
    if ($field.length) {
      const match = doSearch(note.quote, $field[0], note.quote_offset, note.id);
      if (match) {
        const $wrap = highliteRange(match, 'moderation-note-highlight');

        // This allows notes to be found by their ID.
        $wrap.attr('data-moderation-note-highlight-id', note.id);
        $wrap.data('moderation-note', note);

        const $viewTooltip = Drupal.moderation_note.view_tooltip;

        $wrap.on('mouseover.moderation_note', function onMouseOverNote() {
          showViewTooltip($viewTooltip, $(this));
          $viewTooltip.stop().fadeIn();
          clearTimeout(viewTooltipTimeout);
        });

        $wrap.on('mouseleave.moderation_note', function onMouseLeaveNote() {
          clearTimeout(viewTooltipTimeout);
          viewTooltipTimeout = setTimeout(function viewToolTipSetTimeout() {
            $viewTooltip.fadeOut('fast');
          }, 500);
        });
      }
    }
  }

  /**
   * Command to add a Moderation Note.
   *
   * @param {Drupal.Ajax} [ajax]
   *   The ajax object.
   * @param {Object} response
   *   Object holding the server response.
   * @param {Object} response.note
   *   An object representing a moderation note.
   */
  Drupal.AjaxCommands.prototype.add_moderation_note =
    function addModerationNote(ajax, response) {
      const { note } = response;
      Drupal.moderation_note.notes[note.id] = note;
      showModerationNote(note);
    };

  /**
   * Makes another AJAX call after the reply form is submitted to re-load it.
   *
   * @param {Drupal.Ajax} [ajax]
   *   The ajax object.
   * @param {Object} response
   *   Object holding the server response.
   * @param {String} response.id
   *   The ID for the moderation note.
   */
  Drupal.AjaxCommands.prototype.reply_moderation_note =
    function replyModerationNote(ajax, response) {
      const replyAjax = Drupal.ajax({
        url: Drupal.formatString(Drupal.url('moderation-note/!id/reply'), {
          '!id': response.id,
        }),
        dialogType: 'dialog.off_canvas',
        progress: { type: 'fullscreen' },
      });
      replyAjax.execute();
    };

  /**
   * Builds a URL based on a given field ID.
   *
   * Identical to Drupal.quickedit.utils.buildUrl.
   *
   * @param {Number} id
   *   A field ID, as provied by moderation_note_preprocess_field().
   * @param {String} urlFormat
   *   A string with placeholders matching field ID parts.
   * @return {String}
   *  The built URL.
   */
  function buildUrl(id, urlFormat) {
    const parts = id.split('/');
    return Drupal.formatString(decodeURIComponent(urlFormat), {
      '!entity_type': parts[0],
      '!id': parts[1],
      '!field_name': parts[2],
      '!langcode': parts[3],
      '!view_mode': parts[4],
    });
  }

  /**
   * Displays the tooltip at a position relative to the current Range.
   *
   * @param {Object} $tooltip
   *   The tooltip.
   * @param {String} fieldId
   *   The field ID.
   */
  function showAddTooltip($tooltip, fieldId) {
    const selection = window.getSelection();
    const range = selection.getRangeAt(0);
    const rect = range.getBoundingClientRect();
    const top = rect.top - ($tooltip.outerHeight() + 5);
    const left = rect.left + rect.width / 2 - $tooltip.outerWidth() / 2;
    $tooltip.css(
      'left',
      left + document.documentElement.scrollLeft || document.body.scrollLeft,
    );
    $tooltip.css(
      'top',
      top + document.documentElement.scrollTop || document.body.scrollTop,
    );

    const url = buildUrl(
      fieldId,
      Drupal.url(
        'moderation-note/add/!entity_type/!id/!field_name/!langcode/!view_mode',
      ),
    );
    $tooltip.attr('href', url);
    changeAjaxUrl($tooltip, url);

    $tooltip.fadeIn('fast');
  }

  /**
   * Removes all moderation notes from the page.
   */
  function removeModerationNotes() {
    $('.moderation-note-highlight').each(function removeEachHighlight() {
      removeHighlight($(this));
    });
  }

  // We use timeouts to throttle calls to this event.
  let timeout;
  $(document).on('selectionchange', function documentSelectionChanged() {
    clearTimeout(timeout);
    const $addTooltip = Drupal.moderation_note.add_tooltip;
    $addTooltip.fadeOut('fast');

    timeout = setTimeout(function changeTimeoutToolTip() {
      if (window.getSelection) {
        const selection = window.getSelection();
        const text = selection.toString();
        if (text.length) {
          // Ensure that this selection is contained inside a field wrapper.
          const range = selection.getRangeAt(0);
          const $ancestor = $(range.commonAncestorContainer);
          const $field = $ancestor.closest(
            '[data-moderation-note-field-id][data-moderation-note-can-create]',
          );
          if ($field.length) {
            // Show the tooltip.
            showAddTooltip(
              $addTooltip,
              $field.data('moderation-note-field-id'),
            );

            // Store the current selection so that it can be added to the form
            // later.
            const offset = getCursorPositionInTextOf($field[0], range);
            Drupal.moderation_note.selection.quote = text;
            Drupal.moderation_note.selection.quote_offset = offset;
            Drupal.moderation_note.selection.field_id = $field.data(
              'moderation-note-field-id',
            );
          }
        }
      }
    }, 500);
  });

  $(document).on('dialogclose', function dialogCloseRemoveHighlights() {
    removeContextHighlights();
  });

  /**
   * Contains all Moderation Note behaviors.
   *
   * @type {Drupal~behavior}
   */
  Drupal.behaviors.moderation_note = {
    attach(context, settings) {
      // Auto-fill the new note form with the current selection.
      const $newForm = $('[data-moderation-note-new-form]', context);
      if ($newForm.length) {
        const { selection } = Drupal.moderation_note;
        $newForm.find('.field-moderation-note-quote').val(selection.quote);
        $newForm
          .find('.field-moderation-note-quote-offset')
          .val(selection.quote_offset);
        showContextHighlight(selection);
      }

      // On page load, display all notes given to us.
      if (settings.moderation_notes) {
        const notes = settings.moderation_notes;
        delete settings.moderation_notes;

        Object.keys(notes).forEach((i) => {
          Drupal.moderation_note.notes[i] = notes[i];
          showModerationNote(notes[i]);
        });
      }

      if (Drupal.quickedit && Drupal.quickedit.collections.entities) {
        once('moderation-note-quickedit', 'body').forEach(
          function eachNoteQuickEdit() {
            // Toggle moderation note visibility based on Quick Edit's status.
            Drupal.quickedit.collections.entities.on(
              'change:isActive',
              function quickEditIsActive(model, isActive) {
                if (isActive) {
                  removeModerationNotes();
                } else {
                  Object.values(Drupal.moderation_note.notes).forEach(
                    (note) => {
                      showModerationNote(note);
                    },
                  );
                }
                $('body').toggleClass(
                  'moderation-note-quickedit-active',
                  isActive,
                );
              },
            );
            // After a Quick Edit entity is saved, show moderation notes.
            Drupal.quickedit.collections.entities.on(
              'change:isCommitting',
              function quickEditCommitting(model, isCommitting) {
                if (!isCommitting) {
                  removeModerationNotes();
                  Object.values(Drupal.moderation_note.notes).forEach(
                    (note) => {
                      showModerationNote(note);
                    },
                  );
                }
              },
            );
          },
        );
      }

      // Reveal the normally hidden quote context if a fuzzy match was made.
      $('[data-moderation-note-id]').each(function eachNoteId() {
        if (
          Drupal.moderation_note.fuzzy_matches.indexOf(
            this.dataset.moderationNoteId,
          ) !== -1
        ) {
          $(this)
            .find('.moderation-note-quote-information')
            .css('display', 'block');
        }
      });

      // Auto-open a note, if applicable.
      once('moderation-note-open', 'body').forEach(function eachOpenNote() {
        if (typeof URLSearchParams !== 'undefined') {
          const query = new URLSearchParams(window.location.search);
          if (query.has('open-moderation-note')) {
            const id = query.get('open-moderation-note');
            const $element = $(`[data-moderation-note-highlight-id="${id}"]`);
            if ($element.length) {
              showViewTooltip(Drupal.moderation_note.view_tooltip, $element);
              Drupal.moderation_note.view_tooltip
                .stop()
                .hide()
                .trigger('click');
              $('html, body').animate(
                {
                  scrollTop: $element.offset().top - $(window).height() / 2,
                },
                1000,
              );
            }
          }
        }
      });
    },
  };

  $(document).ready(function documentOnReady() {
    $(window).on({
      'dialog:beforecreate': function dialogBeforeCreate(
        event,
        dialog,
        $element,
        settings,
      ) {
        if (
          $element.find(
            '.moderation-note-form-wrapper,[data-moderation-note-id]',
          ).length
        ) {
          settings.dialogClass += ' ui-dialog-off-canvas';
        }
      },
    });
  });
})(Drupal, jQuery, once);
