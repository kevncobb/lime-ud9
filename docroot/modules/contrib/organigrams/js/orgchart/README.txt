README
======

The OrgChart library provides the ability to draw complex organization charts
with HTML5 canvas.

Interface
=========

         +----------
         |  root   |
         +---------+
              |
 +---------+  |  +---------+
 | 'l' box |--+--| 'r' box |
 +---------+  |  +---------+
              |
         +----------
         | 'u' box |
         +----------


Example use
===========

// Set up a new orgchart.
var o = new orgChart();

// Set the colors for all nodes below this function.
o.setColor(
  border_color,
  border_color_hover,
  background_color,
  background_color_hover,
  font_color,
  font_color_hover,
  line_color
);

// Set font settings for all nodes below this function.
o.setFont(
  font_name,
  font_size,
  line_height,
  font_color,
  vertical_alignment
);

// Set dimensions and spacing for all nodes below this function.
o.setSize(
  node_width,
  node_height,
  horizontal_space,
  vertical_space,
  horizontal_offset
);

// Set radius and shadow for all nodes below this function.
o.setNodeStyle(
  top_radius,
  bottom_radius,
  shadow_offset
);

// Add a node to the chart. Call this function multiple times to add more
// nodes to your chart. You can use the functions mentioned above before
// every addNode() function if you'd like to change node styles.
o.addNode(
  id,
  parent,
  position,
  text,
  bold_border,
  url,
  border_color,
  border_color_hover,
  background_color,
  background_color_hover,
  font_color,
  font_color_hover,
  image_url.length > 0 ? image_url : '',
  image_alignment
);

// Draw the chart to your canvas, make sure you have one in your HTML.
o.drawChart(
  canvas_id
  width,
  height,
  position
);
