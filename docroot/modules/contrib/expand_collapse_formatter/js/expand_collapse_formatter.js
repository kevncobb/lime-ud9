/**
 * @file
 * Javacript for the expand_collapse_formatter module.
 */

((Drupal, once) => {
  /**
   * Generate the expand_collapse_formatter.
   *
   * @param {object} field
   *   The field wrapper on which the expand/collapse to apply.
   * @param {string} delta
   *   The delta of field.
   */
  function generateExpandCollapseFormatter(field, delta) {
    const formatter = new Drupal.ExpandCollapseFormatter(field, delta);

    // Attach click event to toggle link.
    if (typeof formatter.toggleLink !== 'undefined') {
      formatter.toggle();

      formatter.toggleLink.addEventListener('click', (event) => {
        event.preventDefault();
        formatter.toggle();
      });
    }
  }

  Drupal.behaviors.expandCollapseFormatter = {
    attach(context) {
      once('expand-toggle', '.expand-collapse', context).forEach(
        generateExpandCollapseFormatter,
      );
    },
  };

  /**
   * Constructor for Drupal.expandCollapseFormatter.
   *
   * @param {object} field
   *   The field wrapper on which the expand/collapse to apply.
   * @param {string} delta
   *   The delta of field.
   */
  Drupal.ExpandCollapseFormatter = function ecfConstructor(field, delta) {
    this.id = `expand-collapse-${delta}`;
    this.content = field.querySelector('.ec-content');
    this.trimLength = field.getAttribute('data-trim-length');
    this.state = field.getAttribute('data-default-state');
    this.linkTextOpen = field.getAttribute('data-link-text-open');
    this.linkTextClose = field.getAttribute('data-link-text-close');
    this.linkClassOpen = field.getAttribute('data-link-class-open');
    this.linkClassClose = field.getAttribute('data-link-class-close');
    this.text = this.content.innerText;
    this.html = this.content.innerHTML;
    this.showMore = Drupal.t(this.linkTextOpen);
    this.showLess = Drupal.t(this.linkTextClose);

    // Set an id for the field element.
    field.setAttribute('id', this.id);

    // Create a read more link and initiate the toggle.
    if (this.text.length > this.trimLength) {
      this.toggleLink = document.createElement('a');
      this.toggleLink.innerHTML = this.showMore;
      this.toggleLink.setAttribute('href', `#${this.id}`);
      this.toggleLink.setAttribute(
        'class',
        `ec-toggle-link ${this.linkClassOpen}`,
      );
      // Insert the read more link after the content.
      this.content.after(this.toggleLink);
      // Initiate expand/collapse.
      this.toggle();
    }
  };

  /**
   * Toggle function to expand or collapse the field.
   *
   * @type {{toggle: Drupal.ExpandCollapseFormatter.toggle}}
   */
  Drupal.ExpandCollapseFormatter.prototype = {
    toggle() {
      let content = '';
      let linkText = '';
      let linkClass = '';

      if (this.state === 'expanded') {
        // Trim the content to a predefined number of characters.
        content = this.trimText(this.html);

        linkText = this.showMore;
        linkClass = `ec-toggle-link ${this.linkClassOpen}`;
        this.state = 'collapsed';
      } else {
        content = this.html;
        linkText = this.showLess;
        linkClass = `ec-toggle-link ${this.linkClassClose}`;
        this.state = 'expanded';
      }

      this.content.innerHTML = content;
      this.toggleLink.innerText = linkText;
      this.toggleLink.setAttribute('class', linkClass);
    },
    trimText(text) {
      let trimmed = text.substring(0, this.trimLength);
      trimmed = trimmed.substring(
        0,
        Math.min(trimmed.length, trimmed.lastIndexOf(' ')),
      );
      trimmed += ' ...';

      return trimmed;
    },
  };
})(Drupal, once);
