/**
 * @file
 * Initiate organigrams.
 */

(function ($) {
  'use strict';
  Drupal.behaviors.organigrams = {
    attach: function (context, settings) {

      var redrawOnResize = {};
      var organigramSettingsFieldPrefix = 'organigrams_';
      var organigramItemsFieldPrefix = 'field_o_';

      /**
       * IE8 fix.
       *
       * @param {Object} obj
       *   Object to get the keys from.
       *
       * @return {Array}
       *   Array with object keys.
       */
      if (!Object.keys) {
        Object.keys = function (obj) {
          var keys = [];

          for (var i in obj) {
            if (obj.hasOwnProperty(i)) {
              keys.push(i);
            }
          }

          return keys;
        };
      }

      // Start loading organigrams.
      organigramLoader();

      /**
       * Load all the registered organigrams.
       */
      function organigramLoader() {
        var organigram_id;
        var organigram_data;

        // Are there organigrams that need processing?
        if (organigramSettingExists('organigrams')) {
          // Iterate through the organigram callbacks.
          for (organigram_id in drupalSettings.organigrams.organigrams) {
            // Guard-for-in.
            if (drupalSettings.organigrams.organigrams.hasOwnProperty(organigram_id) && !organigramExists(organigram_id)) {
              // Retrieve the callback function.
              organigram_data = drupalSettings.organigrams.organigrams[organigram_id];
              // Loading the organigram chart happens in three phases.
              // 1. Hide content and perform additional preload logic.
              organigramPreLoad(organigram_id);
              // 2. Extract node data from the organigram list items.
              organigram_data['nodes'] = organigramExtractNodes(organigram_id);
              // 3. Load the organigram chart.
              organigramLoad(organigram_id, organigram_data);
            }
          }
        }
      }

      /**
       * Prepare the DOM model for the javascript organigram chart.
       *
       * @param {string} organigram_id - Unique organigram hash.
       */
      function organigramPreLoad(organigram_id) {
        // Retrieve the organigram items container.
        var organigram_items_container = $('div.organigram-' + organigram_id);
        // Check if the organigram items container exists.
        if (organigram_items_container != null) {
          // Hide organigram content container.
          organigram_items_container.find('ul').addClass('visually-hidden');
          // Insert the container for the Google Chart which will represent the
          // organigram.
          organigram_items_container.once().prepend('<canvas id="organigram-chart-' + organigram_id + '-canvas" class="organigram-chart"></canvas>');
        }
      }

      /**
       * Extract node data from organigram list items.
       *
       * @param {string} organigram_id - Unique organigram hash.
       *
       * @return {Array} - Array with node objects.
       */
      function organigramExtractNodes(organigram_id) {
        var i;
        var organigram_items;
        var nodes = [];
        var attributes = [
          'item_id',
          'parent',
          'position',
          'text',
          'bold_border',
          'url',
          'border_color',
          'border_color_hover',
          'background_color',
          'background_color_hover',
          'gradient_color',
          'font_color',
          'font_color_hover',
          'image_url',
          'image_alignment'
        ];

        // Retrieve all li items in the organigram items container.
        organigram_items = $('div.organigram-' + organigram_id).find('li');

        // Check if there are any items.
        if (organigram_items.length > 0) {
          // Iterate through the li items.
          organigram_items.each(function (index, li) {
            // Add a node with all attributes specified in the attributes array.
            nodes[index] = {};
            for (i in attributes) {
              if (attributes.hasOwnProperty(i) && typeof $(li).attr(organigramItemsFieldPrefix + attributes[i]) !== 'undefined') {
                nodes[index][attributes[i]] = $(li).attr(organigramItemsFieldPrefix + attributes[i]);
              }
            }
          });
        }

        return nodes;
      }

      /**
       * Load the organigram chart.
       *
       * @param {string} organigram_id - Unique organigram id.
       * @param {Array} organigram_data - Contains organigram settings and nodes.
       */
      function organigramLoad(organigram_id, organigram_data) {
        // Retrieve the organigram chart container.
        var chart_container = $('#organigram-chart-' + organigram_id + '-canvas');

        // Validate the chart_container.
        if (chart_container.length > 0 && typeof organigram_data['organigram_settings'] !== 'undefined' && typeof organigram_data['nodes'] !== 'undefined' && organigram_data['nodes'].length > 0) {
          // Define vars.
          var i;
          var organigram_width = 'parent';
          var organigram_height = 'auto';
          var organigram_position = 'left';
          var color_property_keys = [];
          var color_properties = {};
          var font_property_keys = [];
          var font_properties = {};
          var size_property_keys = [];
          var size_properties = {};
          var node_property_keys = [];
          var node_properties = {};
          var o = new orgChart();

          // Set global organigram settings.
          if (Object.keys(organigram_data['organigram_settings']).length > 0) {
            // Set the organigram width.
            if (typeof organigram_data['organigram_settings'][organigramSettingsFieldPrefix + 'canvas_width'] !== 'undefined') {
              organigram_width = organigram_data['organigram_settings'][organigramSettingsFieldPrefix + 'canvas_width'];
            }
            // Set the organigram height.
            if (typeof organigram_data['organigram_settings'][organigramSettingsFieldPrefix + 'canvas_height'] !== 'undefined') {
              organigram_height = organigram_data['organigram_settings'][organigramSettingsFieldPrefix + 'canvas_height'];
            }
            // Set the organigram position.
            if (parseInt(organigram_data['organigram_settings'][organigramSettingsFieldPrefix + 'center']) === 1) {
              organigram_position = 'center';
            }

            // Add the canvas to the resize watch list.
            if (organigram_width === 'parent') {
              // Set a max width to restore the organigram to its original size.
              if (typeof organigram_data['max_width'] === 'undefined') {
                organigram_data['max_width'] = chart_container.parent().width();
              }
              redrawOnResize[organigram_id] = organigram_data;
            }

            // Set the color properties.
            color_property_keys = [
              'border_color',
              'border_color_hover',
              'background_color',
              'background_color_hover',
              'font_color',
              'font_color_hover',
              'line_color'
            ];
            color_properties = organigramGetProperties(color_property_keys, organigram_data);
            o.setColors(
              color_properties.border_color,
              color_properties.border_color_hover,
              color_properties.background_color,
              color_properties.background_color_hover,
              color_properties.font_color,
              color_properties.font_color_hover,
              color_properties.line_color
            );

            // Set the font properties.
            font_property_keys = [
              'font_name',
              'font_size',
              'line_height',
              'font_color',
              'vertical_alignment'
            ];
            font_properties = organigramGetProperties(font_property_keys, organigram_data);
            o.setFontStyle(font_properties.font_name, font_properties.font_size, font_properties.line_height, font_properties.font_color, font_properties.vertical_alignment);

            // Set the size properties.
            size_property_keys = [
              'node_width',
              'node_height',
              'horizontal_space',
              'vertical_space',
              'horizontal_offset'
            ];
            size_properties = organigramGetProperties(size_property_keys, organigram_data);
            o.setSize(size_properties.node_width, size_properties.node_height, size_properties.horizontal_space, size_properties.vertical_space, size_properties.horizontal_offset);

            // Set the node style properties.
            node_property_keys = [
              'top_radius',
              'bottom_radius',
              'shadow_offset'
            ];
            node_properties = organigramGetProperties(node_property_keys, organigram_data);
            o.setNodeStyle(node_properties.top_radius, node_properties.bottom_radius, node_properties.shadow_offset);
          }

          // Iterate through the nodes.
          for (i in organigram_data['nodes']) {
            if (organigram_data['nodes'].hasOwnProperty(i)) {
              o.createNode(
                organigram_data['nodes'][i].item_id,
                organigram_data['nodes'][i].parent,
                organigram_data['nodes'][i].position,
                organigram_data['nodes'][i].text,
                organigram_data['nodes'][i].bold_border,
                organigram_data['nodes'][i].url,
                organigram_data['nodes'][i].border_color,
                organigram_data['nodes'][i].border_color_hover,
                organigram_data['nodes'][i].background_color,
                organigram_data['nodes'][i].background_color_hover,
                organigram_data['nodes'][i].font_color,
                organigram_data['nodes'][i].font_color_hover,
                organigram_data['nodes'][i].gradient_color,
                organigram_data['nodes'][i].image_url,
                organigram_data['nodes'][i].image_alignment
              );
            }
          }

          // Generate the organigram.
          o.generateChart($(chart_container).attr('id'), organigram_width, organigram_height, organigram_position);
        }
      }

      /**
       * Check if an organigram is already created.
       *
       * @param {string} organigram_id - Unique organigram id.
       *
       * @return {boolean} - Indicating if the organigram exists.
      */
      function organigramExists(organigram_id) {
        if ($('#organigram-chart-' + organigram_id + '-canvas').length > 0) {
          return true;
        }
        return false;
      }

      /**
       * Check if the specified organigram setting is present.
       *
       * @param {string} name - The name of an organigram.
       *
       * @return {boolean} - Indicating if the organigram exists.
       */
      function organigramSettingExists(name) {
        return drupalSettings.organigrams !== null &&
          drupalSettings.organigrams[name] !== null;
      }

      /**
       * Replace properties in the given array with data from the organigram_data.
       *
       * @param {Array} property_keys - Array with keys to look for in the organigram_data array.
       * @param {Array} organigram_data - Array with all organigram data.
       *
       * @return {Object} - An object with the properties set in the property_keys.
       */
      function organigramGetProperties(property_keys, organigram_data) {
        var properties = {};

        for (var i in property_keys) {
          if (!property_keys.hasOwnProperty(i)) {
            continue;
          }

          if (typeof organigram_data['organigram_settings'][organigramSettingsFieldPrefix + property_keys[i]] !== 'undefined' && organigram_data['organigram_settings'][organigramSettingsFieldPrefix + property_keys[i]].length > 0) {
            properties[property_keys[i]] = organigram_data['organigram_settings'][organigramSettingsFieldPrefix + property_keys[i]];

            // Make sure an integer is an integer.
            if (!isNaN(parseInt(properties[property_keys[i]]))) {
              properties[property_keys[i]] = parseInt(properties[property_keys[i]]);
            }
          }
        }
        return properties;
      }

    }
  };
}(jQuery));
