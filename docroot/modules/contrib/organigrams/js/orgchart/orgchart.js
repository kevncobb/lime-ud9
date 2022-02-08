/**
 * @file
 * Contains OrgChart Library v2.0.
 *
 * Original version: 1.13 by J. van Loenen found here:
 * https://jvloenen.home.xs4all.nl/orgchart/sample.htm.
 */

/* exported orgChart */

// So non-IE won't freak out.
var G_vmlCanvasManager;

/**
 * Initialize the orgChart.
 */
function orgChart() {

  'use strict';

  // Default values.
  // Color of the connection lines (global for all lines).
  var lineColor = '#3388DD';
  // Box width (global for all boxes).
  var boxWidth = 120;
  // Box height (global for all boxes).
  var boxHeight = 30;
  // Horizontal space in between the boxes (global for all boxes).
  var hSpace = 30;
  // Vertical space in between the boxes (global for all boxes).
  var vSpace = 20;
  // The number of pixels vertical siblings are shifted (global for all boxes).
  // Half the size of hSpace is best practice.
  var hShift = 15;
  // Default box line color.
  var boxLineColor = '#B5D9EA';
  // Default box line hover color.
  var boxLineColorHover = '';
  // Default box fill color.
  var boxFillColor = '#CFE8EF';
  // Default box fill hover color.
  var boxFillColorHover = '';
  // Default box fill gradient color.
  var gradientColor = '';
  // Default box text color.
  var textColor = '#000000';
  // Default box text hover color.
  var textColorHover = '';
  // Default font.
  var textFont = 'arial';
  // Default text size (pixels, not points).
  var textSize = 12;
  // Default line height in pixels.
  var textLineHeight = 18;
  // Default text alignment.
  var textVAlign = 1;
  // Default text padding.
  var textPadding = 16;
  // Default new line symbol.
  var breakSymbol = '|';

  var curshadowOffsetX = 3;
  var curshadowOffsetY = 3;
  var shadowColor = '#A1A1A1';
  var curtopradius = 5;
  var curbotradius = 5;
  var nodes = [];
  var canvasWidth = 'auto';
  var canvasHeight = 'auto';
  var canvasAlign = 'l';
  var theCanvas;
  var centerParentOverCompleteTree = 0;
  // Experimental; lines may lose connections.
  var maxLoop = 9;
  var minDistBetweenLineAndBox = 5;
  var noalerts = 0;
  // Usibs grouped by parent and vpos.
  var usibsPerLine = {};

  // Internal functions.
  var drawChartPriv;
  var orgChartMouseMove;
  var orgChartResetCursor;
  var orgChartClick;
  var orgChartResize;
  var hShiftTree;
  var hShiftTreeAndRBrothers;
  var fillParentix;
  var checkLines;
  var checkLinesRec;
  var checkOverlap;
  var countSiblings;
  var positionBoxes;
  var positionTree;
  var reposParents;
  var reposParentsRec;
  var findRightMost;
  var findRightMostAtVpos;
  var findLeftMost;
  var findNodeOnLine;
  var drawNode;
  var drawImageNodes;
  var drawConLines;
  var getNodeAt;
  var getEndOfDownline;
  var getNodeAtUnequal;
  var makeRoomForDownline;
  var underVSib;
  var cleanText;
  var getLowestBox;
  var getRootNode;
  var getUParent;
  var nodeUnderParent;
  var getAbsPosX;
  var getAbsPosY;
  var centerOnCanvas;
  var leftOnCanvas;
  var hoverOverNode;
  var removeNode;
  var displayURL;
  var resetChart;

  // Internal information structures.
  var Node = function (id, parent, contype, txt, bold, url, linecolor, linecolorhover, fillcolor, fillcolorhover, textcolor, textcolorhover, fillgradientcolor, imgalign, imgvalign) {
    // User defined id.
    this.id = id;
    // Parent id, user defined.
    this.parent = parent;
    // Parent index in the nodes array, -1 for no parent.
    this.parentix = -1;
    // Options: 'u', 'l', 'r'.
    this.contype = contype;
    // Text for the box.
    this.txt = txt;
    // 1 for bold, 0 if not.
    this.bold = bold;
    // URL.
    this.url = url;
    this.linecolor = linecolor;
    this.linecolorhover = linecolorhover;
    this.fillcolor = fillcolor;
    this.fillcolorhover = fillcolorhover;
    this.fillgradientcolor = fillgradientcolor;
    this.textcolor = textcolor;
    this.textcolorhover = textcolorhover;
    this.textfont = textFont;
    this.textsize = textSize;
    this.textLineHeight = textLineHeight;
    this.valign = textVAlign;
    // Horizontal starting position in pixels.
    this.hpos = -1;
    // Vertical starting position in pixels.
    this.vpos = -1;
    // Contains 'u' siblings.
    this.usib = [];
    // Contains 'r' siblings.
    this.rsib = [];
    // Contains 'l' siblings.
    this.lsib = [];
    // Optional image.
    this.img = '';
    // Image alignment 'l', 'c', 'r'.
    this.imgAlign = imgalign;
    // Image vertical alignment 't', 'm', 'b'.
    this.imgVAlign = imgvalign;
    this.imgDrawn = 0;
    this.topradius = curtopradius;
    this.botradius = curbotradius;
    this.shadowOffsetX = curshadowOffsetX;
    this.shadowOffsetY = curshadowOffsetY;
  };

  /**
   * Generic setting, all boxes will have the same size.
   *
   * @param {int} w
   *   Box width in pixels (optional).
   * @param {int} h
   *   Box height in pixels (optional).
   * @param {int} hspace
   *   Horizontal space between boxes (optional).
   * @param {int} vspace
   *   Vertical space between boxes (optional).
   * @param {int} hshift
   *   Horizontal shift for 'l' and 'r' boxes (optional).
   */
  orgChart.prototype.setSize = function (w, h, hspace, vspace, hshift) {
    if (typeof w !== 'undefined' && w > 0) {
      boxWidth = w;
    }
    if (typeof h !== 'undefined' && h > 0) {
      boxHeight = h;
    }
    if (typeof hspace !== 'undefined' && hspace > 0) {
      hSpace = Math.max(3, hspace);
    }
    if (typeof vspace !== 'undefined' && vspace > 0) {
      vSpace = Math.max(3, vspace);
    }
    if (typeof hshift !== 'undefined' && hshift > 0) {
      hShift = Math.max(3, hshift);
    }
  };

  /**
   * Set the corner style and shade for all node from now on.
   *
   * @param {int} toprad
   *   The radius of the corners on the top. 0 for square boxes. Default value
   *   is 5.
   * @param {int} botrad
   *   The radius of the corners on the bottom. 0 for square boxes. Default
   *   value is 5.
   * @param {int} shadow
   *   Offset of the shadow. 0 for no shadow. Default value is 3. No negative
   *   values for this function.
   */
  orgChart.prototype.setNodeStyle = function (toprad, botrad, shadow) {
    if (typeof toprad !== 'undefined' && toprad >= 0) {
      curtopradius = toprad;
    }
    if (typeof botrad !== 'undefined' && botrad >= 0) {
      curbotradius = botrad;
    }
    if (typeof shadow !== 'undefined' && shadow >= 0) {
      curshadowOffsetX = shadow;
      curshadowOffsetY = shadow;
    }
  };

  /**
   * Deprecated: Set the font for nodes from now on.
   *
   * This function provides backwards compatibility for older scripts. Instead,
   * you should use setFontStyle().
   *
   * @param {String} fname
   *   Font name (eq. 'arial').
   * @param {int} size
   *   Font size (in pixels, eg '12').
   * @param {String} color
   *   RGB font color (optional, not changed if omitted).
   * @param {String} valign
   *   Alignment on/off (optional, not changed if omitted).
   */
  orgChart.prototype.setFont = function (fname, size, color, valign) {
    this.setFontStyle(fname, size, textLineHeight, color, valign);
  };

  /**
   * Set the font for nodes from now on.
   *
   * @param {String} fname
   *   Font name (eq. 'arial').
   * @param {int} size
   *   Font size (in pixels, eg '12').
   * @param {int} lineheight
   *   Line height (in pixels, eg '18').
   * @param {String} color
   *   RGB font color (optional, not changed if omitted).
   * @param {String} valign
   *   Alignment on/off (optional, not changed if omitted).
   */
  orgChart.prototype.setFontStyle = function (fname, size, lineheight, color, valign) {
    if (typeof fname !== 'undefined') {
      textFont = fname;
    }
    if (typeof size !== 'undefined' && size > 0) {
      textSize = size;
    }
    if (typeof lineheight !== 'undefined' && lineheight > 0) {
      textLineHeight = lineheight;
    }
    if (typeof color !== 'undefined' && color !== '') {
      textColor = color;
    }
    if (typeof valign !== 'undefined') {
      textVAlign = valign;
    }
    if (textVAlign === 'c' || textVAlign === 'center') {
      textVAlign = 1;
    }
  };

  /**
   * Deprecated: Set the colors for the nodes from now on.
   *
   * This function provides backwards compatibility for older scripts. Instead,
   * you should use setColors().
   *
   * @param {String} l
   *   RGB line color for the boxes (optional, not changed if omitted).
   * @param {String} f
   *   RGB fill color for the boxes (optional, not changed if omitted).
   * @param {String} t
   *   RGB font color for the boxes (optional, not changed if omitted).
   * @param {String} c
   *   RGB line color for the connection lines (optional, not changed if
   *   omitted).
   */
  orgChart.prototype.setColor = function (l, f, t, c) {
    this.setColors(l, boxLineColorHover, f, boxFillColorHover, t, textColorHover, c);
  };

  /**
   * Set the colors for the nodes from now on.
   *
   * @param {String} l
   *   RGB line color for the boxes (optional, not changed if omitted).
   * @param {String} lh
   *   RGB line color for the boxes on hover (optional, not changed if omitted).
   * @param {String} f
   *   RGB fill color for the boxes (optional, not changed if omitted).
   * @param {String} fh
   *   RGB fill color for the boxes on hover (optional, not changed if omitted).
   * @param {String} t
   *   RGB font color for the boxes (optional, not changed if omitted).
   * @param {String} th
   *   RGB font color for the boxes on hover (optional, not changed if omitted).
   * @param {String} c
   *   RGB line color for the connection lines (optional, not changed if
   *   omitted).
   */
  orgChart.prototype.setColors = function (l, lh, f, fh, t, th, c) {
    if (typeof l !== 'undefined' && l !== '') {
      boxLineColor = l;
    }
    if (typeof lh !== 'undefined' && lh !== '') {
      boxLineColorHover = lh;
    }
    if (typeof f !== 'undefined' && f !== '') {
      boxFillColor = f;
    }
    if (typeof fh !== 'undefined' && fh !== '') {
      boxFillColorHover = fh;
    }
    if (typeof t !== 'undefined' && t !== '') {
      textColor = t;
    }
    if (typeof th !== 'undefined' && th !== '') {
      textColorHover = th;
    }
    if (typeof c !== 'undefined' && c !== '') {
      lineColor = c;
    }
  };

  /**
   * Deprecated: Add a node to the chart.
   *
   * This function provides backwards compatibility for older scripts. Instead,
   * you should use createNode().
   *
   * @param {int|String} id
   *   Unique ID of this node. If empty, its text will be used as identifier.
   * @param {int|String} parent
   *   Parent ID of the parent node (-1 for no parent).
   * @param {String} ctype
   *   Connection type to the parent ('u' for under, 'l' for left, 'r' for
   *   right).
   * @param {String} text
   *   The text for the box (optional, none if omitted).
   * @param {int} bold
   *   Bold lines for this box (optional, no bold if omitted).
   * @param {String} url
   *   A link attached to the box (optional, none if omitted).
   * @param {String} linecolor
   *   RGB line color (optional, default value will be used if omitted).
   * @param {String} fillcolor
   *   RGB fill color (optional, default value will be used if omitted).
   * @param {String} textcolor
   *   RGB font color (optional, default value will be used if omitted).
   * @param {String} img
   *   Optional URL to image.
   * @param {String} imgalign
   *   Image alignment L(eft), C(enter), R(ight) + T(op), M(iddle), B(ottom).
   */
  orgChart.prototype.addNode = function (id, parent, ctype, text, bold, url, linecolor, fillcolor, textcolor, img, imgalign) {
    this.createNode(id, parent, ctype, text, bold, url, linecolor, boxLineColorHover, fillcolor, boxFillColorHover, textcolor, textColorHover, '#ffffff', img, imgalign);
  };

  /**
   * Add a node to the chart.
   *
   * @param {int|String} id
   *   Unique ID of this node. If empty, its text will be used as identifier.
   * @param {int|String} parent
   *   Parent ID of the parent node (-1 for no parent).
   * @param {String} ctype
   *   Connection type to the parent ('u' for under, 'l' for left, 'r' for
   *   right).
   * @param {String} text
   *   The text for the box (optional, none if omitted).
   * @param {int} bold
   *   Bold lines for this box (optional, no bold if omitted).
   * @param {String} url
   *   A link attached to the box (optional, none if omitted).
   * @param {String} linecolor
   *   RGB line color (optional, default value will be used if omitted).
   * @param {String} linecolorhover
   *   RGB line color on hover (optional, default value will be used if
   *   omitted).
   * @param {String} fillcolor
   *   RGB fill color (optional, default value will be used if omitted).
   * @param {String} fillcolorhover
   *   RGB fill color on hover (optional, default value will be used if
   *   omitted).
   * @param {String} textcolor
   *   RGB font color (optional, default value will be used if omitted).
   * @param {String} textcolorhover
   *   RGB font color on hover (optional, default value will be used if
   *   omitted).
   * @param {String} fillgradientcolor
   *   RGB color used as gradient.
   * @param {String} img
   *   Optional URL to image.
   * @param {String} imgalign
   *   Image alignment L(eft), C(enter), R(ight) + T(op), M(iddle), B(ottom).
   */
  orgChart.prototype.createNode = function (id, parent, ctype, text, bold, url, linecolor, linecolorhover, fillcolor, fillcolorhover, textcolor, textcolorhover, fillgradientcolor, img, imgalign) {
    var imgvalign;

    if (typeof id === 'undefined') {
      id = '';
    }
    if (typeof parent === 'undefined') {
      parent = '';
    }
    if (typeof ctype === 'undefined') {
      ctype = 'u';
    }
    if (typeof bold === 'undefined') {
      bold = 0;
    }
    if (typeof text === 'undefined') {
      text = '';
    }
    if (typeof url === 'undefined') {
      url = '';
    }
    if (!linecolor) {
      linecolor = boxLineColor;
    }
    if (!fillcolor) {
      fillcolor = boxFillColor;
    }
    if (!textcolor) {
      textcolor = textColor;
    }
    if (typeof fillgradientcolor === 'undefined') {
      fillgradientcolor = gradientColor;
    }
    if (typeof imgalign === 'undefined') {
      imgalign = 'lm';
    }

    if (id === '') {
      id = text;
    }
    if (parent === '') {
      ctype = 'u';
    }
    ctype = ctype.toLowerCase();
    if (ctype !== 'u' && ctype !== 'l' && ctype !== 'r' && parent !== '') {
      ctype = 'u';
    }
    imgvalign = 'm';
    if (imgalign.substr(1, 1) === 't' || imgalign.substr(1, 1) === 'T') {
      imgvalign = 't';
    }
    if (imgalign.substr(1, 1) === 'b' || imgalign.substr(1, 1) === 'B') {
      imgvalign = 'b';
    }
    if (imgalign.substr(0, 1) === 'c' || imgalign.substr(0, 1) === 'C') {
      imgalign = 'c';
    }
    if (imgalign.substr(0, 1) === 'm' || imgalign.substr(0, 1) === 'M') {
      imgalign = 'c';
    }
    if (imgalign.substr(0, 1) === 'r' || imgalign.substr(0, 1) === 'R') {
      imgalign = 'r';
    }
    if (imgalign !== 'c' && imgalign !== 'r') {
      imgalign = 'l';
    }

    var i;
    for (i = 0; i < nodes.length; i++) {
      if (nodes[i].id === id && noalerts !== 1) {
        alert('Duplicate node. Node ' + (1 + nodes.length) + ', id = ' + id + ', \'' + text + '\'\nAlready defined as node ' + i + ', \'' + nodes[i].txt + '\'\n\nThis new node will not be added.\nNo additional messages are given.');
        noalerts = 1;
        return;
      }
    }

    var n = new Node(id, parent, ctype, text, bold, url, linecolor, linecolorhover, fillcolor, fillcolorhover, textcolor, textcolorhover, fillgradientcolor, imgalign, imgvalign);
    if (typeof img !== 'undefined' && img !== '') {
      n.img = new Image();
      n.img.src = img;
      n.img.onload = function () {
        drawImageNodes();
      };
    }

    nodes[nodes.length] = n;
  };

  /**
   * Deprecated: Draws the chart on the canvas.
   *
   * This function provides backwards compatibility for older scripts. Instead,
   * you should use generateChart().
   *
   * @param {String} id
   *   ID of the canvas.
   * @param {String} align
   *   Contains 'c' of 'center' for horizontal alignment on the canvas or 'l'
   *   for left alignment (left is default if omitted).
   * @param {Boolean} fit
   *   Deprecated: Indicates whether the canvas should be resized to fit the
   *   width and height of the chart. This parameter is not used anymore.
   */
  orgChart.prototype.drawChart = function (id, align, fit) {
    // Backwards compatibility: linebreaks at \n sequence.
    breakSymbol = '\n';
    this.generateChart(id, 'auto', 'auto', align);
  };

  /**
   * Draws the chart on the canvas.
   *
   * @param {String} id
   *   ID of the canvas.
   * @param {int|String} width
   *   Integer in pixels for a static with, 'auto' for automatic calculation
   *   and 'parent' to use the container element's with.
   * @param {int|String} height
   *   Same as width.
   * @param {String} align
   *   Contains 'c' of 'center' for horizontal alignment on the canvas or 'l'
   *   for left alignment (left is default if omitted).
   */
  orgChart.prototype.generateChart = function (id, width, height, align) {
    drawChartPriv(id, true, width, height, align);
  };

  /**
   * Re-draws the in-memory chart on the canvas.
   *
   * Resizing a canvas clears the content.
   *
   * @param {String} id
   *   ID of the canvas.
   */
  orgChart.prototype.redrawChart = function (id) {
    drawChartPriv(id, false);
  };

  /**
   * Draw the chart.
   *
   * @param {String} id
   *   Unique canvas ID.
   * @param {Boolean} repos
   *   Boolean indicating if the chart must be repositioned.
   * @param {String|int} width
   *   Integer for a static width, 'auto' for auto calculation and 'parent' for
   *   matching the width to its parent element.
   * @param {String|int} height
   *   The same as width.
   * @param {String} align
   *   How to align the chart on the canvas ('c' for center or 'l' for left).
   */
  drawChartPriv = function (id, repos, width, height, align) {
    var i;
    var ctx;
    var devicePixelRatio;
    var backingStoreRatio;
    var cwidth;
    var cheight;
    var ratio;
    // Set default canvas translation to 0.5 to prevent 1px line anti aliasing.
    // HTTP://www.graph.net/docs/howto-get-crisp-lines-with-no-antialias.html.
    var translateX = 0.5;
    var translateY = 0.5;

    // Set global vars to be used for a redraw.
    if (typeof width !== 'undefined') {
      canvasWidth = width;
    }
    if (typeof height !== 'undefined') {
      canvasHeight = height;
    }
    if (typeof align !== 'undefined') {
      canvasAlign = align;
    }

    theCanvas = document.getElementById(id);
    if (!theCanvas) {
      alert('Canvas id \'' + id + '\' not found');
      return;
    }

    // IE.
    if (typeof G_vmlCanvasManager !== 'undefined') {
      G_vmlCanvasManager.initElement(theCanvas);
    }

    ctx = theCanvas.getContext('2d');

    ctx.lineWidth = 1;
    ctx.fillStyle = boxFillColor;
    ctx.strokeStyle = boxLineColor;

    // Calculate the canvas width.
    var maxW = 0;
    // Try to get the width from the parent element if it exists.
    if (width === 'parent' && theCanvas.parentElement !== null) {
      maxW = theCanvas.parentElement.offsetWidth;
    }
    // Set a fixed width.
    else if (width !== 'auto' && width > 0) {
      maxW = width;
    }
    // Set the new canvas width. Add 1 to fix the half pixel bug by which lines
    // are blurred.
    if (maxW > 0) {
      theCanvas.width = parseInt(maxW) + 1;
      theCanvas.style.width = parseInt(maxW) + 1 + 'px';
    }

    // Calculate the canvas height.
    var maxH = 0;
    // Try to get the height from the parent element if it exists.
    if (height === 'parent' && theCanvas.parentElement !== null) {
      maxH = theCanvas.parentElement.offsetHeight;
    }
    // Set a fixed height.
    else if (height !== 'auto' && height > 0) {
      maxH = height;
    }
    // Set the new canvas height. Add 1 to fix the half pixel bug by which lines
    // are blurred.
    if (maxH > 0) {
      theCanvas.height = parseInt(maxH) + 1;
      theCanvas.style.height = parseInt(maxH) + 1 + 'px';
    }

    if (repos) {
      // Clear all existing node positions when repositioning.
      for (i = 0; i < nodes.length; i++) {
        nodes[i].hpos = -1;
        nodes[i].vpos = -1;
        nodes[i].usib = [];
        nodes[i].rsib = [];
        nodes[i].lsib = [];
      }

      fillParentix();
      countSiblings();
      positionBoxes(width);
      checkOverlap();
      checkLines();
      reposParents();
    }

    // If no width is set, calculate it from the node data.
    if (maxW === 0) {
      for (i = 0; i < nodes.length; i++) {
        if (nodes[i].hpos + boxWidth + nodes[i].shadowOffsetX > maxW) {
          maxW = nodes[i].hpos + boxWidth + nodes[i].shadowOffsetX;
        }
      }
      // Overwrite the canvas width. Add 1 to make sure the most right border
      // does not fall off the screen.
      if (maxW > 0) {
        theCanvas.width = maxW + 1;
        theCanvas.style.width = maxW + 1 + 'px';
      }
    }

    // If no height is set, calculate it from the node data.
    if (maxH === 0) {
      for (i = 0; i < nodes.length; i++) {
        if (nodes[i].vpos + boxHeight + nodes[i].shadowOffsetY > maxH) {
          maxH = nodes[i].vpos + boxHeight + nodes[i].shadowOffsetY;
        }
      }
      // Overwrite the canvas height. Add 1 to make sure the lowest border
      // does not fall off the screen.
      if (maxH > 0) {
        theCanvas.height = maxH + 1;
        theCanvas.style.height = maxH + 1 + 'px';
      }
    }

    // After all width calculations are done and all nodes are positioned,
    // set the minimum width of the canvas to match the position of the most
    // right node.
    if (width === 'parent' && theCanvas.parentElement !== null) {
      var mostRightNodePos = 0;
      for (i = 0; i < nodes.length; i++) {
        if (nodes[i].hpos + boxWidth + nodes[i].shadowOffsetX > mostRightNodePos) {
          mostRightNodePos = nodes[i].hpos + boxWidth + nodes[i].shadowOffsetX;
        }
      }
      if (mostRightNodePos > theCanvas.parentElement.offsetWidth) {
        theCanvas.width = mostRightNodePos + 1;
        theCanvas.style.width = mostRightNodePos + 1 + 'px';
      }
    }

    if (align === 'c' || align === 'center') {
      centerOnCanvas(theCanvas.width);
    }
    else {
      leftOnCanvas();
    }

    // High dpi displays.
    if ('devicePixelRatio' in window && theCanvas.width !== 0) {
      devicePixelRatio = window.devicePixelRatio || 1;
      backingStoreRatio = ctx.webkitBackingStorePixelRatio ||
        ctx.mozBackingStorePixelRatio ||
        ctx.msBackingStorePixelRatio ||
        ctx.oBackingStorePixelRatio ||
        ctx.backingStorePixelRatio || 1;

      ratio = devicePixelRatio / backingStoreRatio;
      cwidth = theCanvas.width;
      cheight = theCanvas.height;

      if (ratio !== 1) {
        theCanvas.width = cwidth * ratio;
        theCanvas.height = cheight * ratio;

        theCanvas.style.width = cwidth + 'px';
        theCanvas.style.height = cheight + 'px';

        ctx.scale(ratio, ratio);
      }
    }

    // Don't perform a translation on the x axis when the most left node of the
    // first tree has a non integer x position.
    var mostLeftNodeX = findLeftMost(0);
    if (parseInt(mostLeftNodeX) !== mostLeftNodeX) {
      translateX = 0;
    }
    // Don't perform a translation on the y axis when the first node of the
    // tree has a non integer y position.
    var mostTopNodeY = nodes[0].vpos;
    if (parseInt(mostTopNodeY) !== mostTopNodeY) {
      translateY = 0;
    }

    // Fix for blurred (anti aliased) lines.
    ctx.translate(translateX, translateY);

    // Draw the lines.
    drawConLines(ctx);

    // Draw the boxes.
    for (i = 0; i < nodes.length; i++) {
      drawNode(ctx, i);
    }

    // Add click behaviour.
    if (theCanvas.addEventListener) {
      // Remove old listeners.
      theCanvas.removeEventListener('click', orgChartClick, false);
      theCanvas.removeEventListener('mousemove', orgChartMouseMove, false);
      theCanvas.removeEventListener('mouseout', orgChartResetCursor, false);
      window.removeEventListener('resize', orgChartResize, false);
      // Add new listeners.
      theCanvas.addEventListener('click', orgChartClick, false);
      theCanvas.addEventListener('mousemove', orgChartMouseMove, false);
      theCanvas.addEventListener('mouseout', orgChartResetCursor, false);
      window.addEventListener('resize', orgChartResize, false);
    }
    // IE.
    else if (theCanvas.attachEvent) {
      theCanvas.onclick = function () {
        var mtarget = document.getElementById(id);
        orgChartClick(event, mtarget.scrollLeft, mtarget.scrollTop - 20);
      };

      theCanvas.onmousemove = function () {
        var mtarget = document.getElementById(id);
        orgChartMouseMove(event, mtarget.scrollLeft, mtarget.scrollTop - 20);
      };

      theCanvas.onmouseout = function () {
        orgChartResetCursor();
      };

      window.onresize = function () {
        orgChartResize();
      };
    }

    window.onload = function () {
      orgChartResize();
    };
  };

  /**
   * Turns the mouse cursor in a pointer when hovering over a sibling with a
   * URL.
   *
   * @param {Object} event
   *   The mouse event.
   */
  orgChartMouseMove = function (event) {
    var x;
    var y;
    var i;

    x = event.clientX;
    y = event.clientY;

    x -= getAbsPosX(theCanvas);
    y -= getAbsPosY(theCanvas);

    if (document.documentElement && document.documentElement.scrollLeft) {
      x += document.documentElement.scrollLeft;
    }
    else {
      x += document.body.scrollLeft;
    }
    if (document.documentElement && document.documentElement.scrollTop) {
      y += document.documentElement.scrollTop;
    }
    else {
      y += document.body.scrollTop;
    }

    i = getNodeAt(x, y);
    if (i >= 0 && nodes[i].url.length > 0) {
      document.body.style.cursor = 'pointer';
      hoverOverNode(i);
    }
    else {
      document.body.style.cursor = 'default';
      resetChart();
    }
  };

  /**
   * Changed the background and text color of a node.
   *
   * @param {int} i
   *   A node index.
   */
  hoverOverNode = function (i) {
    // Check if the mouse is not yet hovering above this node to prevent this
    // code from executing multiple times.
    if (typeof nodes[i].hover === 'undefined' || !nodes[i].hover) {
      var ctx = theCanvas.getContext('2d');
      // Remove the original node.
      removeNode(ctx, i);
      // Add a property to the node so we know we are hovering.
      nodes[i].hover = true;

      // Backup the original data.
      nodes[i].origlinecolor = nodes[i].linecolor;
      nodes[i].origfillcolor = nodes[i].fillcolor;
      nodes[i].origtextcolor = nodes[i].textcolor;

      // Set new linecolor. First try to set it with the default hover color.
      if (typeof boxLineColorHover !== 'undefined' && boxLineColorHover !== '') {
        nodes[i].linecolor = boxLineColorHover;
      }
      // Then check if there is a node override.
      if (typeof nodes[i].linecolorhover !== 'undefined' && nodes[i].linecolorhover !== '') {
        nodes[i].linecolor = nodes[i].linecolorhover;
      }

      // Set new fillcolor. First try to set it with the default hover color.
      if (typeof boxFillColorHover !== 'undefined' && boxFillColorHover !== '') {
        nodes[i].fillcolor = boxFillColorHover;
      }
      // Then check if there is a node override.
      if (typeof nodes[i].fillcolorhover !== 'undefined' && nodes[i].fillcolorhover !== '') {
        nodes[i].fillcolor = nodes[i].fillcolorhover;
      }

      // Set new textcolor. First try to set it with the default hover color.
      if (typeof textColorHover !== 'undefined' && textColorHover !== '') {
        nodes[i].textcolor = textColorHover;
      }
      // Then check if there is a node override.
      if (typeof nodes[i].textcolorhover !== 'undefined' && nodes[i].textcolorhover !== '') {
        nodes[i].textcolor = nodes[i].textcolorhover;
      }

      // Draw the node again with its altered colors.
      drawNode(ctx, i);

      displayURL(i);
    }
  };

  /**
   * Adds a div containing the node URL to the page.
   *
   * @param {int} i
   *   Index of a node.
   */
  displayURL = function (i) {
    var base_url = '';
    var path = '';
    var url = nodes[i].url;

    // Load the base path from the current location.
    if (typeof window.location.origin !== 'undefined') {
      base_url = window.location.origin;
    }
    // Load the path without the base path from the current location.
    if (typeof window.location.pathname !== 'undefined') {
      path = window.location.pathname;
    }

    // Check if a protocol (like http://) is defined in the URL.
    if (url.indexOf('://') < 0 || url.indexOf('/') < url.indexOf('://')) {
      // Add the base url to the URL if it starts with a slash.
      if (url.substring(0, 1) === '/') {
        url = base_url + url;
      }
      else {
        // Append the URL to the current location.
        url = base_url + path + url;
      }
    }

    // Create a div to contain the URL.
    var hover_div = document.createElement('div');
    // Add content to that div.
    hover_div.innerHTML = url;
    // Give it an ID.
    hover_div.setAttribute('id', 'node-' + i + '-hover');
    // Give it a class.
    hover_div.setAttribute('class', 'node-hover');
    // @TODO: Add this to the configuration.
    // Position the div in the bottom left corner just like Chrome.
    hover_div.setAttribute('style', 'position: fixed; z-index: 999; bottom: 0px; left: 0px; background-color: #ffffff; border: 1px solid #cccccc; display: inline; padding: 2px; font-size: 10px;');
    // Add the div to the HTML.
    document.body.appendChild(hover_div);
  };

  /**
   * Reset all nodes to their original state.
   */
  resetChart = function () {
    var i;
    var redraw = false;
    var hover_divs;

    // Iterate through all nodes.
    for (i = 0; i < nodes.length; i++) {
      // If a node was hovered, reset its data.
      if (typeof nodes[i].hover !== 'undefined' && nodes[i].hover) {
        nodes[i].hover = false;
        if (typeof nodes[i].origfillcolor !== 'undefined') {
          nodes[i].linecolor = nodes[i].origlinecolor;
          nodes[i].fillcolor = nodes[i].origfillcolor;
          nodes[i].textcolor = nodes[i].origtextcolor;
        }
        // Set redraw if minimal one node was hovered.
        redraw = true;
      }
    }

    // Redraw the chart.
    if (redraw) {
      drawChartPriv(theCanvas.getAttribute('id'), true, canvasWidth, canvasHeight, canvasAlign);
    }

    // Remove all URL divs.
    hover_divs = document.getElementsByClassName('node-hover');
    for (i in hover_divs) {
      if (hover_divs.hasOwnProperty(i)) {
        hover_divs[i].parentNode.removeChild(hover_divs[i]);
      }
    }
  };

  /**
   * Remove a node at a certain position.
   *
   * @param {Object} ctx
   *   The context of a canvas.
   * @param {int} i
   *   A node index.
   */
  removeNode = function (ctx, i) {
    // Load the node data. Add and subtract 1 for the border.
    var x = nodes[i].hpos - 1 + nodes[i].topradius;
    var y = nodes[i].vpos - 1;
    var width = boxWidth + 1;
    var height = boxHeight + 1;
    var bold = nodes[i].bold;
    // It's a double border when bold.
    if (bold === 1) {
      x -= 1;
      y -= 1;
      width += 1;
      height += 1;
    }

    // Remove the node.
    ctx.clearRect(x, y, width, height);
  };

  /**
   * Reset the mouse cursor.
   *
   * @param {Object} event
   *   The mouse event.
   */
  orgChartResetCursor = function (event) {
    if (document.body.style.cursor === 'pointer') {
      document.body.style.cursor = 'default';
    }
    resetChart();
  };

  /**
   * Handles the click event.
   *
   * @param {Object} event
   *   The mouse event.
   * @param {int} offsetx
   *   The horizontal offset of the mouse in pixels.
   * @param {int} offsety
   *   The vertical offset of the mouse in pixels.
   */
  orgChartClick = function (event, offsetx, offsety) {
    var x;
    var y;
    var i;
    var i1;
    var i2;
    var url;

    if (event.button < 0 || event.button > 1) {
      // Left button (w3c: 0, IE: 1) only.
      return;
    }

    x = event.clientX;
    y = event.clientY;

    x -= getAbsPosX(theCanvas);
    y -= getAbsPosY(theCanvas);

    if (document.documentElement && document.documentElement.scrollLeft) {
      x += document.documentElement.scrollLeft;
    }
    else {
      x += document.body.scrollLeft;
    }
    if (document.documentElement && document.documentElement.scrollTop) {
      y += document.documentElement.scrollTop;
    }
    else {
      y += document.body.scrollTop;
    }

    // Get the clicked node's index.
    i = getNodeAt(x, y);
    if (i >= 0) {
      // Check if this node has a URL.
      if (nodes[i].url.length > 0) {
        url = nodes[i].url;

        // If the URL starts with 'orgchart:', a local action should be
        // performed, e.g. 'orgchart:addNode:8|1|u|Test'.
        if (url.indexOf('orgchart:') >= 0) {
          // Remove 'orgchart:' from the URL.
          url = url.substring(9);
          if (url.indexOf(':')) {
            // Get the action and params.
            var action = url.substring(0, url.indexOf(':'));
            var params = url.substring(url.indexOf(':') + 1);

            if (typeof action !== 'undefined' && action !== '' && typeof params !== 'undefined' && params !== '') {
              params = params.split('|');

              // Execute the action.
              if (action === 'addNode' && params.length === 4) {
                // @TODO: This should be replaced by prototype function addNode.
                nodes[nodes.length] = new Node(params[0], params[1], params[2], params[3], 0, '', '#000000', '', '#ffffff', '', '#000000', '', 'lt', 'c');
                // Redraw the chart.
                drawChartPriv(theCanvas.getAttribute('id'), true);
              }
            }
          }
        }
        else {
          document.body.style.cursor = 'default';
          i1 = url.indexOf('://');
          i2 = url.indexOf('/');
          // Open the URL in a new window if it contains '://'.
          if (i1 >= 0 && i2 > i1) {
            window.open(url);
          }
          else {
            window.location = url;
          }
        }
      }
    }
  };

  /**
   * Handles the window resize event.
   *
   * Redraws the canvas if its width is set to 'parent'.
   *
   * @param {Object} event
   *   The resize event.
   */
  orgChartResize = function (event) {
    if (canvasWidth === 'parent' && theCanvas.parentElement !== null) {
      theCanvas.width = theCanvas.parentElement.offsetWidth;
      drawChartPriv(theCanvas.getAttribute('id'), true, canvasWidth, canvasHeight, canvasAlign);

      // Show a scroll bar if the canvas can't be made smaller.
      theCanvas.parentElement.style.overflowX = 'hidden';
      theCanvas.parentElement.style.overflowY = 'hidden';
      if (theCanvas.width - 1 > theCanvas.parentElement.offsetWidth) {
        theCanvas.parentElement.style.overflowX = 'auto';
      }
    }
  };

  /**
   * Shift all siblings (which have a position already) 'w' pixels.
   *
   * @param {int} p
   *   The index ID of the parent sib.
   * @param {int} w
   *   Width to shift right in pixels.
   */
  hShiftTree = function (p, w) {
    var s;

    if (nodes[p].hpos >= 0) {
      nodes[p].hpos += w;
    }

    for (s = 0; s < nodes[p].usib.length; s++) {
      hShiftTree(nodes[p].usib[s], w);
    }

    for (s = 0; s < nodes[p].lsib.length; s++) {
      hShiftTree(nodes[p].lsib[s], w);
    }

    for (s = 0; s < nodes[p].rsib.length; s++) {
      hShiftTree(nodes[p].rsib[s], w);
    }
  };

  /**
   * Shift this tree to the right.
   * If this is an 'u' sib, also shift all brothers which are to the right too.
   * (In which case we shift all other root nodes too).
   *
   * @param {int} p
   *   The index ID of the parent sib.
   * @param {int} w
   *   Width to shift right in pixels.
   */
  hShiftTreeAndRBrothers = function (p, w) {
    var i;
    var q;
    var s;
    var hpos;
    var rp;

    rp = getRootNode(p);
    hpos = nodes[rp].hpos;

    if (nodes[p].contype === 'u' && nodes[p].parent !== '') {
      q = nodes[p].parentix;
      for (s = nodes[q].usib.length - 1; s >= 0; s--) {
        hShiftTree(nodes[q].usib[s], w);
        if (nodes[q].usib[s] === p) {
          break;
        }
      }
    }
    else {
      hShiftTree(p, w);
    }

    if (nodes[p].contype === 'u') {
      for (i = 0; i < nodes.length; i++) {
        if (i !== rp && nodes[i].parent === '' && nodes[i].hpos > hpos) {
          hShiftTree(i, w);
        }
      }
    }
  };

  /**
   * Fill all nodes with the index of the parent.
   */
  fillParentix = function () {
    var i;
    var j;
    for (i = 0; i < nodes.length; i++) {
      if (nodes[i].parent !== '') {
        for (j = 0; j < nodes.length; j++) {
          if (nodes[i].parent === nodes[j].id) {
            nodes[i].parentix = j;
            break;
          }
        }
        if (nodes[i].parentix === -1) {
          nodes[i].parent = '';
        }
      }
    }
  };

  /**
   * Check all vertical lines for crossing boxes. If so, shift to the right.
   */
  checkLines = function () {
    var p;

    for (p = 0; p < nodes.length; p++) {
      if (nodes[p].parent === '') {
        checkLinesRec(p);
      }
    }
  };

  checkLinesRec = function (p) {
    var s;
    var r;
    var x;
    var y;
    var y2;
    var n;
    var m;
    var rp;
    var rs;
    var v;
    var w;
    var branch;
    var tm;

    y = 0;

    // Check lsib, the latest is the lowest point.
    n = nodes[p].lsib.length;
    if (n > 0) {
      s = nodes[p].lsib[n - 1];
      y = nodes[s].vpos + boxHeight / 2;
    }

    // Check rsib, the latest is the lowest point.
    n = nodes[p].rsib.length;
    if (n > 0) {
      s = nodes[p].rsib[n - 1];
      y2 = nodes[s].vpos + boxHeight / 2;
      y = Math.max(y, y2);
    }

    // If usib, the lowest point is even lower.
    n = nodes[p].usib.length;
    if (n > 0) {
      s = nodes[p].usib[0];
      y = nodes[s].vpos - vSpace / 2;
    }

    if (y > 0) {
      for (n = nodes[p].vpos + boxHeight / 2 + boxHeight + vSpace; n <= y; n += boxHeight + vSpace) {
        m = 0;
        do {
          s = getNodeAt(nodes[p].hpos + boxWidth / 2 - minDistBetweenLineAndBox, n);
          if (s >= 0) {
            // If the node found is a sib of the box with the downline,
            // shifting the parent doesn't help.
            w = nodes[s].hpos + boxWidth + hSpace / 2 - (nodes[p].hpos + boxWidth / 2);
            rp = s;
            while (nodes[rp].parent !== '' && rp !== p) {
              rp = nodes[rp].parentix;
            }
            if (rp !== p) {
              // Find the parent of s on the same vpos as p to decide what to
              // shift.
              rs = s;
              while (nodes[rs].parent !== '' && nodes[rs].vpos > nodes[p].vpos) {
                rs = nodes[rs].parentix;
              }
              rp = p;
              while (nodes[rp].parent !== '' && nodes[rp].contype !== 'u') {
                rp = nodes[rp].parentix;
              }
              if (nodes[rs].hpos > nodes[p].hpos) {
                hShiftTreeAndRBrothers(rs, w);
              }
              else {
                hShiftTreeAndRBrothers(rp, w);
              }
            }
            else {
              branch = nodes[s].contype;
              tm = s;
              while (nodes[tm].parentix !== '' && nodes[tm].parentix !== p) {
                tm = nodes[tm].parentix;
              }
              branch = nodes[tm].contype;

              rs = getRootNode(s);
              rp = getRootNode(p);
              if (rs === rp) {
                if (branch === 'l') {
                  // Use the hShift is it's bigger.
                  if (hShift >= hSpace) {
                    w = nodes[s].hpos + boxWidth + hShift - (nodes[p].hpos + boxWidth / 2);
                  }
                  else {
                    w = nodes[s].hpos + boxWidth + hSpace / 2 - (nodes[p].hpos + boxWidth / 2);
                  }
                  while (nodes[p].parentix !== '' && nodes[p].contype !== 'u') {
                    p = nodes[p].parentix;
                  }
                  hShiftTreeAndRBrothers(p, w);
                  hShiftTree(tm, -w);

                  // Move rsibs back to the left as far as possible.
                  v = getEndOfDownline(p);
                  for (r = 0; r < nodes[p].rsib.length; r++) {
                    if (nodes[nodes[p].rsib[r]].hpos >= 0) {
                      x = findLeftMost(nodes[p].rsib[r], v);
                      // If the leftmost is the r-sib itself or hShift is bigger
                      // than hSpace, use the default
                      // hShift distance. Use hSpace otherwise.
                      if (x === nodes[p].rsib[r].hpos || hShift >= hSpace) {
                        w = nodes[p].hpos + boxWidth / 2 + hShift - x;
                      }
                      else {
                        w = nodes[p].hpos + boxWidth / 2 + hSpace / 2 - x;
                      }
                      if (w) {
                        hShiftTree(nodes[p].rsib[r], w);
                      }
                    }
                  }

                  // Find the most right usib.
                  var maxx = 0;
                  var i;
                  for (i = 0; i < nodes[p].usib.length; i++) {
                    x = findRightMost(nodes[p].usib[i], 99999);
                    maxx = Math.max(x, maxx);
                  }

                  // Move usibs back to the left as far as possible if the most
                  // right usib falls out of the canvas.
                  if (maxx + boxWidth > theCanvas.width && canvasWidth !== 'auto') {
                    for (var u = 0; u < nodes[p].usib.length; u++) {
                      var usib = nodes[p].usib[u];
                      // Do nothing if the hpos is already 0.
                      if (nodes[usib].hpos > 0) {
                        // Find the most left x in this usib tree.
                        x = findLeftMost(usib);

                        // Do nothing if the hpos is already 0.
                        if (x > 0) {
                          // Get the lowest vpos in the usib tree.
                          v = getEndOfDownline(usib);
                          if (v < 0) {
                            // Use the usib vpos if nothing is found.
                            v = nodes[usib].vpos;
                          }

                          // Look for nodes on the left.
                          var o = findNodeOnLine(v, x, 'l');
                          if (o < 0) {
                            // If no nodes are found, shift the usib to hpos 0.
                            w = -x;
                          }
                          else {
                            // Set the hpos next to the found node.
                            w = nodes[o].hpos + boxWidth + hSpace - x;
                          }
                          // Shift the usib with its children.
                          if (w) {
                            hShiftTree(usib, w);
                          }
                        }
                      }
                    }
                  }
                }
                else {
                  w = (nodes[p].hpos + boxWidth / 2) - nodes[s].hpos + hSpace;
                  hShiftTreeAndRBrothers(tm, w);
                }
              }
              else {
                if (nodes[rp].hpos > nodes[rs].hpos) {
                  hShiftTree(rp, w);
                }
                else {
                  hShiftTree(rs, w);
                }
              }
            }
          }
          m++;
        } while (s >= 0 && m < maxLoop);
      }
    }

    // Check the siblings.
    for (s = 0; s < nodes[p].usib.length; s++) {
      checkLinesRec(nodes[p].usib[s]);
    }
    for (s = 0; s < nodes[p].lsib.length; s++) {
      checkLinesRec(nodes[p].lsib[s]);
    }
    for (s = 0; s < nodes[p].rsib.length; s++) {
      checkLinesRec(nodes[p].rsib[s]);
    }
  };

  checkOverlap = function () {
    var i;
    var j;
    var retry;
    var m;
    var ui;
    var uj;
    var w;

    // Boxes direct on top of another box?
    m = 0;
    retry = 1;
    while (m < maxLoop && retry) {
      retry = 0;
      m++;
      for (i = 0; i < nodes.length; i++) {
        for (j = i + 1; j < nodes.length; j++) {
          if (nodes[i].hpos === nodes[j].hpos && nodes[i].vpos === nodes[j].vpos) {
            ui = getRootNode(i);
            uj = getRootNode(j);
            if (ui !== uj) {
              hShiftTreeAndRBrothers(uj, boxWidth + hSpace);
            }
            else {
              ui = getUParent(i);
              uj = getUParent(j);
              if (ui !== uj) {
                hShiftTreeAndRBrothers(uj, boxWidth + hSpace);
              }
              else {
                // In the right subtree, find the first 'u' or 'r' parent to
                // shift.
                uj = j;
                while (nodes[uj].parent !== '' && nodes[uj].contype !== 'u' && nodes[uj].contype !== 'r') {
                  uj = nodes[uj].parentix;
                }
                if (nodes[uj].parent !== '') {
                  hShiftTreeAndRBrothers(uj, boxWidth + hSpace);
                }
              }
            }
            retry = 1;
          }
        }
      }
    }

    // Small overlap?
    m = 0;
    retry = 1;
    while (m < maxLoop && retry) {
      retry = 0;
      m++;
      for (i = 0; i < nodes.length; i++) {
        j = getNodeAtUnequal(nodes[i].hpos + minDistBetweenLineAndBox, nodes[i].vpos + boxHeight / 2, i);
        if (j >= 0) {
          ui = getUParent(i);
          uj = getUParent(j);
          if (ui !== uj) {
            if (nodes[ui].hpos > nodes[uj].hpos) {
              uj = ui;
            }
            if (nodes[i].hpos > nodes[j].hpos) {
              w = nodes[j].hpos - nodes[i].hpos + boxWidth + hSpace;
            }
            else {
              w = nodes[i].hpos - nodes[j].hpos + boxWidth + hSpace;
            }
            if (nodeUnderParent(i, ui) && nodeUnderParent(j, ui)) {
              j = i;
              while (j >= 0 && nodes[j].contype === nodes[i].contype) {
                j = nodes[j].parentix;
              }
              if (j >= 0) {
                hShiftTreeAndRBrothers(j, w);
              }
            }
            else {
              while (nodes[ui].parent !== '' && nodes[ui].contype === 'u' && nodes[nodes[ui].parentix].usib.length === 1) {
                ui = nodes[ui].parentix;
              }
              hShiftTreeAndRBrothers(ui, w);
            }
            retry = 1;
          }
          else {
            hShiftTreeAndRBrothers(i, boxWidth / 2);
            retry = 1;
          }
        }
      }
    }
  };

  countSiblings = function () {
    var i;
    var p;
    var h;
    var v;

    for (i = 0; i < nodes.length; i++) {
      p = nodes[i].parentix;
      if (p >= 0) {
        if (nodes[i].contype === 'u') {
          h = nodes[p].usib.length;
          nodes[p].usib[h] = i;
        }
        if (nodes[i].contype === 'l') {
          v = nodes[p].lsib.length;
          nodes[p].lsib[v] = i;
        }
        if (nodes[i].contype === 'r') {
          v = nodes[p].rsib.length;
          nodes[p].rsib[v] = i;
        }
      }
    }
  };

  /**
   * Position all nodes.
   *
   * @param {int|String} width
   *   The width of the canvas in pixels or 'auto' for automatic calculation
   *   or 'parent' to match the parent element's width.
   */
  positionBoxes = function (width) {
    var i;
    var j;
    var x;
    var y;
    var maxx;
    var lowestBox;

    // Position all top level boxes.
    // The starting pos is 'x'. After the tree is positioned, center it.
    x = 0;
    y = 0;
    for (i = 0; i < nodes.length; i++) {
      // If there is no more room on the right side, place the root node below
      // the previous root node.
      if (nodes[i].parent === '') {
        // Get the most right x of the chart.
        maxx = x + nodes[i].shadowOffsetX + boxWidth;
        // Check if we've encountered the next parent and if the width is not
        // auto or check the parent element's width when set to parent.
        if (i > 0 && ((width !== 'auto' && maxx > width) || (width === 'parent' && theCanvas.parentElement !== null && maxx > theCanvas.parentElement.offsetWidth))) {
          x = 0;
          y = 0;
          // Iterate backwards to get the lowest box of all trees.
          for (j = i - 1; j >= 0; j--) {
            lowestBox = getLowestBox(j) + boxHeight + vSpace * 2;
            // Set the y position of this root node to the lowest box.
            if (lowestBox > y) {
              y = lowestBox;
            }
          }
        }

        nodes[i].hpos = x + nodes[i].shadowOffsetX;
        nodes[i].vpos = y + nodes[i].shadowOffsetY;
        positionTree(i);
        // Var hpos can be changed during positionTree. Set the start for the
        // next tree.
        x = findRightMost(i) + boxWidth + hSpace;
      }
    }
  };

  /**
   * Position the complete tree under this parent.
   * Var p has a position already. Position 'l', 'r' and 'u' sibs:
   *
   * @param {int} p
   *   The index of the parent sib.
   */
  positionTree = function (p) {
    var h;
    var v;
    var s;
    var o;
    var i;
    var j;
    var n;
    var w;
    var q;
    var r;
    var us;
    var uo;
    var x;
    var x1;
    var x2;
    var y;
    var l;

    // Positioning all 'l' sibs.
    for (v = 0; v < nodes[p].lsib.length; v++) {
      s = nodes[p].lsib[v];
      // New lsib, so the downline crosses all the way down. Make room first.
      y = getLowestBox(p, 'l') + boxHeight + vSpace;
      makeRoomForDownline(p, y);

      nodes[s].hpos = nodes[p].hpos - boxWidth / 2 - hShift;
      nodes[s].vpos = y;
      if (nodes[s].hpos < 0) {
        for (r = 0; r < nodes.length; r++) {
          if (nodes[r].parent === '') {
            hShiftTree(r, -nodes[s].hpos);
          }
        }
        nodes[s].hpos = 0;
      }

      // Overlap?
      n = 1;
      do {
        o = getNodeAtUnequal(nodes[s].hpos - minDistBetweenLineAndBox, nodes[s].vpos + minDistBetweenLineAndBox, s);
        if (o < 0) {
          o = getNodeAtUnequal(nodes[s].hpos + boxWidth + minDistBetweenLineAndBox, nodes[s].vpos + minDistBetweenLineAndBox, s);
        }
        if (o < 0) {
          o = findNodeOnLine(nodes[s].vpos, 999999, 'l');
          if (o === s) {
            o = -1;
          }
        }
        if (o >= 0) {
          w = nodes[o].hpos + boxWidth + hSpace - nodes[s].hpos;
          q = nodes[s].parentix;
          while (q !== -1 && nodes[q].contype !== 'u') {
            q = nodes[q].parentix;
          }
          if (q < 0) {
            hShiftTree(p, w);
          }
          else {
            if (!nodeUnderParent(o, q)) {
              hShiftTreeAndRBrothers(q, w);
            }
          }
        }
        n++;
        if (n > maxLoop) {
          o = -1;
        }
      } while (o >= 0);
      positionTree(s);
    }

    // Positioning all rsibs.
    for (v = 0; v < nodes[p].rsib.length; v++) {
      s = nodes[p].rsib[v];
      // nodes[s].hpos = nodes[p].hpos + boxWidth / 2 + hShift;
      // Default placement: right from the parent and right from all other
      // nodes in this row.
      nodes[s].vpos = getLowestBox(p, 'r') + boxHeight + vSpace;
      x1 = findRightMostAtVpos(nodes[s].vpos);
      if (x1 > 0) {
        x1 = x1 + boxWidth + hSpace;
      }
      x2 = nodes[p].hpos + boxWidth / 2 + hShift;
      nodes[s].hpos = Math.max(x1, x2);

      // Overlap?
      n = 1;
      do {
        o = getNodeAtUnequal(nodes[s].hpos - minDistBetweenLineAndBox, nodes[s].vpos + minDistBetweenLineAndBox, s);
        if (o < 0) {
          o = getNodeAtUnequal(nodes[s].hpos + boxWidth + minDistBetweenLineAndBox, nodes[s].vpos + minDistBetweenLineAndBox, s);
        }
        if (o < 0) {
          o = findNodeOnLine(nodes[s].vpos + 5, 999999, 'l');
          if (o === s) {
            o = -1;
          }
        }
        if (o >= 0) {
          h = nodes[s].hpos - nodes[o].hpos;
          h = Math.abs(h);
          q = nodes[s].parentix;
          while (q !== -1 && nodes[q].contype !== 'u') {
            q = nodes[q].parentix;
          }
          if (q < 0) {
            hShiftTree(p, boxWidth + hSpace - h);
          }
          else {
            us = getUParent(s);
            uo = getUParent(o);
            if (us === uo) {
              // Shift parent if overlap with lsib of our parent.
              if (!nodeUnderParent(o, q)) {
                hShiftTreeAndRBrothers(q, boxWidth + hSpace - h);
              }
            }
            else {
              // Shift the common parent (if any) to the right, and the
              // uppermost parent of the existing o node back to the left.
              us = getRootNode(s);
              uo = getRootNode(o);
              w = nodes[o].hpos - nodes[s].hpos + boxWidth + hSpace;
              if (us === uo) {
                us = s;
                while (nodes[us].parent !== '' && !nodeUnderParent(o, nodes[us].parentix)) {
                  us = nodes[us].parentix;
                }
                // Only shift if a parent is found.
                if (us !== s) {
                  hShiftTreeAndRBrothers(us, w);
                }
              }
              else {
                hShiftTreeAndRBrothers(s, w);
              }
            }
          }
        }
        n++;
        if (n > maxLoop) {
          o = -1;
        }
      } while (o >= 0);
      positionTree(s);
    }

    // Make room for the downline (if necessary).
    v = getEndOfDownline(p);
    if (v > 0) {
      makeRoomForDownline(p, v);
    }

    // Position all 'u' sibs.
    v = getLowestBox(p, 'lr') + boxHeight + vSpace;
    n = nodes[p].usib.length;

    if (n > 0) {
      // If there is a left or right subtree, the starting position is on the
      // right, the left or in between them.
      for (i = 0; i < nodes[p].lsib.length; i++) {
        x = findRightMost(nodes[p].lsib[i], v);
        if (nodes[p].rsib.length > 0) {
          w = x + hSpace - boxWidth / 2 - nodes[p].hpos;
        }
        else {
          w = x + hShift - boxWidth / 2 - nodes[p].hpos;
        }
        if (w > 0) {
          nodes[p].hpos += w;
          for (l = 0; l < i; l++) {
            hShiftTree(nodes[p].lsib[l], w);
          }
        }
      }

      // If right trees, shift the to the right of the (repositioned) root node.
      for (i = 0; i < nodes[p].rsib.length; i++) {
        x = findLeftMost(nodes[p].rsib[i], v);
        // If the node found is the lsib itself or hShift is bigger than hSpace,
        // use hShift. Otherwise, use hSpace / 2.
        if (x === nodes[nodes[p].rsib[i]].hpos || hShift >= hSpace) {
          w = nodes[p].hpos + boxWidth / 2 + hShift - x;
        }
        else {
          w = nodes[p].hpos + boxWidth / 2 + hSpace / 2 - x;
        }
        if (w) {
          hShiftTree(nodes[p].rsib[i], w);
          x += w;
        }
      }

      // If there are multiple usib nodes, try to place them under the left
      // tree, centering under the parent.
      x1 = nodes[p].hpos;
      x2 = nodes[p].hpos;
      if (n >= 2 && x1 > 0) {
        // Check all node on this vpos to overlap.
        // Maybe we overlap a downline, this will be caught later on.
        h = findNodeOnLine(v, nodes[p].hpos, 'l');
        if (h < 0) {
          x2 = x2 + boxWidth / 2 - (n * boxWidth + (n - 1) * hSpace) / 2;
          if (x2 < 0) {
            x2 = 0;
          }
          x1 = x2;
        }
        if (h >= 0 && nodes[h].hpos + boxWidth + hSpace < x1) {
          // Minimum x.
          x1 = nodes[h].hpos + boxWidth + hSpace;
          x2 = x2 + boxWidth / 2 - (n * boxWidth + (n - 1) * hSpace) / 2;
          if (x1 > x2) {
            x2 = x1;
          }
          else {
            x1 = x2;
          }
        }
      }

      y = getLowestBox(p, 'lr') + boxHeight + vSpace;
      for (h = 0; h < nodes[p].usib.length; h++) {
        s = nodes[p].usib[h];

        nodes[s].hpos = x2;
        nodes[s].vpos = y;

        v = underVSib(s);
        // Overlap?
        n = 0;
        do {
          o = getNodeAtUnequal(nodes[s].hpos - minDistBetweenLineAndBox, nodes[s].vpos + minDistBetweenLineAndBox, s);
          if (o < 0) {
            o = getNodeAtUnequal(nodes[s].hpos + boxWidth + minDistBetweenLineAndBox, nodes[s].vpos + minDistBetweenLineAndBox, s);
          }
          if (o < 0) {
            o = findNodeOnLine(nodes[s].vpos, 999999, 'l');
            if (o === s) {
              o = -1;
            }
          }
          if (o >= 0) {
            w = nodes[o].hpos - nodes[s].hpos + boxWidth + hSpace;
            // Find the highest node, not in the path of the found 'o' node.
            us = s;
            while (nodes[us].parent !== '' && !nodeUnderParent(o, nodes[us].parentix)) {
              us = nodes[us].parentix;
            }
            hShiftTreeAndRBrothers(us, w);

            // Move the usib to the next line if it falls outside of the canvas.
            if (canvasWidth !== 'auto' && nodes[us].hpos + boxWidth > theCanvas.width) {
              // Reset the shifted width.
              nodes[us].hpos = nodes[h].hpos - w;
              if (nodes[us].hpos < 0) {
                nodes[us].hpos = 0;
              }
              // Also remove the hSpace if it's bigger than hSpace.
              if (hSpace < hShift) {
                nodes[us].hpos -= hSpace;
              }
              // Move to the next row.
              nodes[us].vpos = getLowestBox(p) + boxHeight + vSpace;
              // Reposition its children.
              positionTree(us);

              // Check if this usib already exists in the usibsPerLine array
              // and delete it if so.
              for (j in usibsPerLine) {
                if (usibsPerLine.hasOwnProperty(j) && typeof usibsPerLine[j][s] !== 'undefined') {
                  delete usibsPerLine[j][s];
                }
              }
              // Add this usib to the array.
              if (typeof usibsPerLine[y] === 'undefined') {
                usibsPerLine[y] = {};
              }
              usibsPerLine[y][us] = us;
            }
          }
          n++;
          if (n > maxLoop) {
            o = -1;
          }
        } while (o >= 0);
        positionTree(s);

        // Check if this usib already exists in the usibsPerLine array
        // and delete it if so.
        for (j in usibsPerLine) {
          if (usibsPerLine.hasOwnProperty(j) && typeof usibsPerLine[j][s] !== 'undefined') {
            delete usibsPerLine[j][s];
          }
        }
        // Add this usib to the array.
        if (typeof usibsPerLine[y] === 'undefined') {
          usibsPerLine[y] = {};
        }
        usibsPerLine[y][s] = s;

        // Set the x for the next usib.
        x2 = nodes[s].hpos + boxWidth + hSpace;

        // Reset the x and set the y to the next row if the next x2 falls
        // outside of the canvas.
        if (canvasWidth !== 'auto' && x2 + boxWidth > theCanvas.width) {
          x2 = x1;
          y = getLowestBox(p) + boxHeight + vSpace;
        }
      }
    }

    reposParentsRec(p);
  };

  /**
   * All parents with usibs are repositioned (start at the lowest level!).
   */
  reposParents = function () {
    var i;

    for (i = 0; i < nodes.length; i++) {
      if (nodes[i].parentix === -1) {
        reposParentsRec(i);
      }
    }
  };

  reposParentsRec = function (p) {
    var w;
    var s;
    var f;
    var h;
    var r;
    var maxw;
    var minw;
    var q;
    var i;
    var maxxu;
    var x;

    // The siblings first.
    for (s = 0; s < nodes[p].usib.length; s++) {
      reposParentsRec(nodes[p].usib[s]);
    }
    for (s = 0; s < nodes[p].lsib.length; s++) {
      reposParentsRec(nodes[p].lsib[s]);
    }
    for (s = 0; s < nodes[p].rsib.length; s++) {
      reposParentsRec(nodes[p].rsib[s]);
    }

    // If this is a parent with two or more usibs, reposition it (Repos over 1
    // usib too, just correct it if necessary).
    // Except if this is a sib without room to move, limit the room to move.
    // Of course a rsib of this sib can cause an overlap too.
    // Exception: if this is a node with only one usib, we need to position
    // right above the usib. If necessary, we need to move the complete parent
    // tree.
    h = nodes[p].usib.length;
    if (h >= 1) {
      maxw = -1;
      minw = -1;
      if (nodes[p].contype === 'l') {
        r = nodes[p].parentix;
        maxw = nodes[r].hpos + boxWidth / 2 - boxWidth - hSpace - nodes[p].hpos;
      }
      if (nodes[p].contype === 'r') {
        r = nodes[p].parentix;
        minw = nodes[r].hpos + boxWidth / 2 - hSpace - boxWidth - nodes[p].hpos;
      }
      w = 0;
      if (centerParentOverCompleteTree) {
        w = (findRightMost(p) - nodes[p].hpos) / 2;
      }
      else {
        // Get the first usib.
        f = nodes[p].usib[0];

        // Find the most right usib.
        maxxu = 0;
        for (i = 0; i < nodes[p].usib.length; i++) {
          x = nodes[nodes[p].usib[i]].hpos;
          maxxu = Math.max(x, maxxu);
        }

        // Calculate the center of the usibs.
        w = nodes[f].hpos + (maxxu - nodes[f].hpos) / 2 - nodes[p].hpos;
      }

      if (maxw >= 0 && w > maxw) {
        w = maxw;
      }
      if (minw >= 0 && w > minw) {
        w = minw;
      }

      s = findNodeOnLine(nodes[p].vpos, nodes[p].hpos, 'r');
      if (s >= 0) {
        if (nodes[p].hpos + boxWidth + hSpace + w >= nodes[s].hpos) {
          w = nodes[s].hpos - boxWidth - hSpace - nodes[p].hpos;
        }
      }

      if (nodes[p].usib.length === 1 && nodes[p].hpos + w !== nodes[nodes[p].usib[0]].hpos) {
        w = nodes[nodes[p].usib[0]].hpos - nodes[p].hpos;
      }

      // Check for a crossing with a rsib connection line.
      maxw = 999999;
      for (q = 0; q < nodes.length; q++) {
        if (nodes[q].vpos === nodes[p].vpos && nodes[q].hpos > nodes[p].hpos) {
          maxw = nodes[q].hpos - nodes[p].hpos - boxWidth - hShift - hSpace;
          if (maxw < 0) {
            maxw = 0;
          }
          if (w > maxw) {
            w = maxw;
          }
        }
      }

      if (w > 1) {
        // Shift this nodes and all 'l' and 'r' sib trees.
        nodes[p].hpos += w;
        for (s = 0; s < nodes[p].lsib.length; s++) {
          hShiftTree(nodes[p].lsib[s], w);
        }
        for (s = 0; s < nodes[p].rsib.length; s++) {
          hShiftTree(nodes[p].rsib[s], w);
        }
      }
    }
  };

  /**
   * Return the highest hpos of the given tree, if maxv is specified, vpos
   * must be less than maxv.
   *
   * @param {int} p
   *   Index of parent sib.
   * @param {int} maxv
   *   Maximum vpos.
   *
   * @return {*}
   *   Position in pixels of most right sib.
   */
  findRightMost = function (p, maxv) {
    var maxx;
    var x;
    var i;

    if (typeof maxv === 'undefined') {
      maxv = 999999;
    }

    if (nodes[p].vpos <= maxv) {
      maxx = nodes[p].hpos;
    }
    else {
      maxx = -1;
    }

    // Is there a usib to the right?
    for (i = 0; i < nodes[p].usib.length; i++) {
      x = findRightMost(nodes[p].usib[i], maxv);
      maxx = Math.max(x, maxx);
    }

    // Walk along the lsibs.
    for (i = 0; i < nodes[p].lsib.length; i++) {
      x = findRightMost(nodes[p].lsib[i], maxv);
      maxx = Math.max(x, maxx);
    }

    // Walk along the rsibs.
    for (i = 0; i < nodes[p].rsib.length; i++) {
      x = findRightMost(nodes[p].rsib[i], maxv);
      maxx = Math.max(x, maxx);
    }

    return maxx;
  };

  /**
   * Find the highest hpos of any node at vpos 'v'.
   *
   * @param {int} v
   *   Contains a vertical position in pixels.
   *
   * @return {int}
   *   Hpos of the highest node found or -1 if not.
   */
  findRightMostAtVpos = function (v) {
    var maxx = -1;
    var i;

    for (i = 0; i < nodes.length; i++) {
      if (nodes[i].vpos === v && nodes[i].hpos > maxx) {
        maxx = nodes[i].hpos;
      }
    }

    return maxx;
  };

  /**
   * Return the lowest hpos of the given tree.
   *
   * @param {int} p
   *   Index of parent sib.
   * @param {int} maxv
   *   Maximum vpos.
   *
   * @return {*}
   *   Position in pixels of most left sib.
   */
  findLeftMost = function (p, maxv) {
    var minx;
    var x;
    var i;

    if (typeof maxv === 'undefined') {
      maxv = 999999;
    }

    if (nodes[p].vpos <= maxv) {
      minx = nodes[p].hpos;
    }
    else {
      minx = 999999;
    }

    // Is there a usib to the left?
    if (nodes[p].usib.length > 0) {
      x = findLeftMost(nodes[p].usib[0], maxv);
      minx = Math.min(x, minx);
    }

    // Walk along the lsibs.
    for (i = 0; i < nodes[p].lsib.length; i++) {
      x = findLeftMost(nodes[p].lsib[i], maxv);
      minx = Math.min(x, minx);
    }

    // Walk along the rsibs.
    for (i = 0; i < nodes[p].rsib.length; i++) {
      x = findLeftMost(nodes[p].rsib[i], maxv);
      minx = Math.min(x, minx);
    }

    return minx;
  };

  /**
   * Search all nodes on vpos 'v', and return the rightmost node on the left,
   * or the leftmost on the rest, depending on the direction.
   *
   * @param {int} v
   *   Vertical position in pixels.
   * @param {int} h
   *   Horizontal position in pixels.
   * @param {String} dir
   *   Direction 'l' or 'r'.
   *
   * @return {number|*}
   *   Index of a sib.
   */
  findNodeOnLine = function (v, h, dir) {
    var i;
    var fnd;
    var x;

    fnd = -1;
    x = (dir === 'l') ? -1 : 999999;

    for (i = 0; i < nodes.length; i++) {
      if (nodes[i].vpos === v) {
        if (dir === 'l' && nodes[i].hpos < h && nodes[i].hpos > x) {
          fnd = i;
          x = nodes[i].hpos;
        }
        if (dir === 'r' && nodes[i].hpos > h && nodes[i].hpos < x) {
          fnd = i;
          x = nodes[i].hpos;
        }
      }
    }

    return fnd;
  };

  /**
   * Images are loaded after drawing finished.
   * After an image has been loaded, this function will be called, which redraws
   * the nodes with images nodes, have a valid image now and are drawn
   * incomplete before.
   */
  drawImageNodes = function () {
    var i;
    var ctx;

    ctx = theCanvas.getContext('2d');

    for (i = 0; i < nodes.length; i++) {
      if (nodes[i].img && nodes[i].img.width > 0 && !nodes[i].imgDrawn) {
        drawNode(ctx, i);
      }
    }
  };

  drawNode = function (ctx, i) {
    var ix;
    var gradient;
    var maxrad;
    var imgrad;
    var x = nodes[i].hpos;
    var y = nodes[i].vpos;
    var width = boxWidth;
    var height = boxHeight;
    var txt = nodes[i].txt;
    var bold = nodes[i].bold;
    var blcolor = nodes[i].linecolor;
    var bfcolor = nodes[i].fillcolor;
    var gfcolor = nodes[i].fillgradientcolor;
    var tcolor = nodes[i].textcolor;
    var font = nodes[i].textfont;
    var fsize = nodes[i].textsize;
    var flineheight = nodes[i].textLineHeight;
    var valign = nodes[i].valign;
    var img = nodes[i].img;
    var imgalign = nodes[i].imgAlign;
    var imgvalign = nodes[i].imgVAlign;
    var toprad = nodes[i].topradius;
    var botrad = nodes[i].botradius;
    var shadowx = nodes[i].shadowOffsetX;
    var shadowy = nodes[i].shadowOffsetY;

    // Draw shadow with gradient first.
    if (shadowx > 0) {
      x += shadowx;
      y += shadowy;
      ctx.fillStyle = shadowColor;
      ctx.beginPath();
      ctx.moveTo(x + toprad, y);
      ctx.lineTo(x + width - toprad, y);
      if (toprad > 0) {
        ctx.quadraticCurveTo(x + width, y, x + width, y + toprad);
      }
      ctx.lineTo(x + width, y + height - botrad);
      if (botrad > 0) {
        ctx.quadraticCurveTo(x + width, y + height, x + width - botrad, y + height);
      }
      ctx.lineTo(x + botrad, y + height);
      if (botrad > 0) {
        ctx.quadraticCurveTo(x, y + height, x, y + height - botrad);
      }
      ctx.lineTo(x, y + toprad);
      if (toprad > 0) {
        ctx.quadraticCurveTo(x, y, x + toprad, y);
      }
      ctx.closePath();
      ctx.fill();
      x -= shadowx;
      y -= shadowy;
    }

    // Draw the box.
    ctx.lineWidth = (bold === 1) ? 2 : 1;

    // Set a gradient if the gradient color is defined.
    if (gfcolor !== '') {
      gradient = ctx.createLinearGradient(x, y, x, y + height);
      gradient.addColorStop(0, gfcolor);
      gradient.addColorStop(0.7, bfcolor);
      ctx.fillStyle = gradient;
    }
    else {
      // Otherwise, set a plain background color.
      ctx.fillStyle = bfcolor;
    }

    ctx.strokeStyle = blcolor;
    ctx.beginPath();
    ctx.moveTo(x + toprad, y);
    ctx.lineTo(x + width - toprad, y);
    if (toprad > 0) {
      ctx.quadraticCurveTo(x + width, y, x + width, y + toprad);
    }
    ctx.lineTo(x + width, y + height - botrad);
    if (botrad > 0) {
      ctx.quadraticCurveTo(x + width, y + height, x + width - botrad, y + height);
    }
    ctx.lineTo(x + botrad, y + height);
    if (botrad > 0) {
      ctx.quadraticCurveTo(x, y + height, x, y + height - botrad);
    }
    ctx.lineTo(x, y + toprad);
    if (toprad > 0) {
      ctx.quadraticCurveTo(x, y, x + toprad, y);
    }
    ctx.closePath();
    ctx.fill();
    ctx.stroke();

    // Draw the image, if any. If the image is available, draw. Mark it
    // incomplete otherwise.
    var xPic;
    var yPic;
    var maxx;
    var maxy;

    if (img) {
      // Get all positions and sizes, even if no image loaded yet.
      if (img.width > 0) {
        maxx = img.width;
        maxy = img.height;

        // Resize if image too height. If the imgrad is less than the linewidth
        // of the box, we need to draw inside the box.
        imgrad = 0.414 * (toprad + botrad);
        if (imgrad < 1) {
          imgrad = 1;
        }

        if (maxy > height - imgrad) {
          maxx = img.width * (height - imgrad) / img.height;
          maxy = height - imgrad;
        }

        // Resize if image too width, even after previous resize.
        maxrad = toprad;
        if (botrad > maxrad) {
          maxrad = botrad;
        }
        imgrad = 0.414 * maxrad;
        if (maxx > width - 2 * imgrad) {
          maxy = img.height * (width - 2 * imgrad) / img.width;
          maxx = width - 2 * imgrad;
        }
      }
      else {
        imgrad = 0.414 * (toprad + botrad);
        if (imgrad < 1) {
          imgrad = 1;
        }

        if (width > height) {
          maxy = height - 2 * imgrad;
        }
        else {
          maxy = width - 2 * imgrad;
        }
        maxx = maxy;
      }

      // Horizontal offset.
      xPic = imgrad;
      if (imgalign === 'c') {
        xPic = (width - 2 * imgrad - maxx) / 2 + imgrad;
      }
      if (imgalign === 'r') {
        xPic = width - maxx - imgrad;
      }

      // Vertical offset.
      yPic = 0.414 * toprad + 1;
      if (imgvalign === 'm') {
        yPic = (height - maxy) / 2;
      }
      if (imgvalign === 'b') {
        yPic = height - maxy - (0.414 * botrad) - 1;
      }

      if (img.width > 0) {
        ctx.drawImage(img, x + xPic, y + yPic, maxx, maxy);
        nodes[i].imgDrawn = 1;
      }
      else {
        // Draw an image-not-found picture.
        if (maxy > 0) {
          ctx.beginPath();
          ctx.rect(x + xPic, y + yPic, maxx, maxy);
          ctx.fillStyle = '#FFFFFF';
          ctx.fill();
          ctx.lineWidth = 1;
          ctx.strokeStyle = '#000000';
          ctx.stroke();

          ctx.beginPath();
          ctx.moveTo(x + xPic + 1, y + yPic + 1);
          ctx.lineTo(x + xPic + maxx - 1, y + yPic + maxy - 1);
          ctx.strokeStyle = '#FF0000';
          ctx.stroke();

          ctx.beginPath();
          ctx.moveTo(x + xPic + maxx - 1, y + yPic + 1);
          ctx.lineTo(x + xPic + 1, y + yPic + maxy - 1);
          ctx.strokeStyle = '#FF0000';
          ctx.stroke();
        }
        nodes[i].imgDrawn = 0;
      }

      // Adjust the box size, so the text will be placed next to the image.
      // Find the biggest rectangle for the text.
      if (imgalign === 'l') {
        if (imgvalign === 't') {
          if ((width - maxx) * height > width * (height - maxy)) {
            x += (xPic + maxx);
            width -= (xPic + maxx);
          }
          else {
            y += (yPic + maxy);
            height -= (yPic + maxy);
          }
        }
        if (imgvalign === 'm') {
          x += (xPic + maxx);
          width -= (xPic + maxx);
        }
        if (imgvalign === 'b') {
          if ((width - maxx) * height > width * (height - maxy)) {
            x += (xPic + maxx);
            width -= (xPic + maxx);
          }
          else {
            height -= (yPic + maxy);
          }
        }
      }
      if (imgalign === 'c') {
        if (imgvalign === 't') {
          y += (yPic + maxy);
          height -= (yPic + maxy);
        }
        if (imgvalign === 'm') {
          if (width - maxx > height - maxy) {
            x += (xPic + maxx);
            width -= (xPic + maxx);
          }
          else {
            y += (yPic + maxy);
            height -= (yPic + maxy);
          }
        }
        if (imgvalign === 'b') {
          height = yPic;
        }
      }
      if (imgalign === 'r') {
        if (imgvalign === 't') {
          if ((width - maxx) * height > width * (height - maxy)) {
            width = xPic;
          }
          else {
            y += (yPic + maxy);
            height -= (yPic + maxy);
          }
        }
        if (imgvalign === 'm') {
          width = xPic;
        }
        if (imgvalign === 'b') {
          if ((width - maxx) * height > width * (height - maxy)) {
            width = xPic;
          }
          else {
            height -= (yPic + maxy);
          }
        }
      }
    }

    // Draw text, break the string on spaces, and breakSymbol sequences.
    // Note: excanvas does not clip text. We need to do it ourselves.
    // Split text in multiple lines if it doesn't fit.
    var tlines = [];
    var n = 0;
    var t1;
    var nl;

    // The font syntax is: [style] <size> <fontname>. <size> <style> <fontname>
    // does not work! So reformat here.
    var style = '';
    font = font.toLowerCase();
    ix = font.indexOf('bold ');
    if (ix >= 0) {
      font = font.substr(0, ix) + font.substr(ix + 5);
      style = 'bold ';
    }
    ix = font.indexOf('italic ');
    if (ix >= 0) {
      font = font.substr(0, ix) + font.substr(ix + 5);
      style += 'italic ';
    }
    ctx.font = style + fsize + 'px ' + font;
    ctx.textBaseline = 'top';
    ctx.textAlign = 'center';
    ctx.fillStyle = tcolor;

    txt = cleanText(txt);
    while (txt.length > 0 && n < maxLoop) {
      t1 = txt;
      // Split on | first.
      nl = t1.indexOf(breakSymbol);
      if (nl >= 0) {
        t1 = t1.substr(0, nl);
      }
      // Remove words until the string fits.
      ix = t1.lastIndexOf(' ');
      while (ctx.measureText(t1).width > width - textPadding && ix > 0) {
        t1 = t1.substr(0, ix);
        ix = t1.lastIndexOf(' ');
      }
      tlines[n] = t1;
      n++;
      if (t1.length < txt.length) {
        txt = txt.substr(t1.length);
        // Remove the breakSymbol from the text.
        if (nl >= 0 && txt.substr(0, breakSymbol.length) === breakSymbol) {
          txt = txt.substr(breakSymbol.length);
        }
      }
      else {
        txt = '';
      }
    }

    // IE does not clip text, so we clip it here.
    if (flineheight > fsize && flineheight * n > height) {
      n = Math.floor(height / flineheight);
    }
    else if (fsize * n > height) {
      n = Math.floor(height / fsize);
    }

    var yp = 0;
    if (valign) {
      yp = Math.floor((height - n * fsize) / 2);
    }

    // Recalculate the starting y position if a lineheight bigger that the font
    // size is set.
    if (flineheight > fsize) {
      if (valign) {
        yp = Math.floor((height - n * flineheight) / 2);
      }
      yp += (flineheight - fsize) / 2;
    }

    for (i = 0; i < n; i++) {
      while (tlines[i].length > 0 && ctx.measureText(tlines[i]).width > width) {
        tlines[i] = tlines[i].substr(0, tlines[i].length - 1);
      }
      ctx.fillText(tlines[i], x + width / 2, y + yp);

      // Increase the y position with the lineheight if it's bigger than the
      // font size so the next line is drawn correctly.
      if (flineheight > fsize) {
        yp += flineheight;
      }
      else {
        yp += parseInt(fsize, 10);
      }
    }
  };

  /**
   * Draw all connection lines. We cannot simply draw all lines, over and over
   * again, as the color will change. Therefore we draw all lines separate,
   * and only once.
   *
   * @param {Object} ctx
   *   Canvas context.
   */
  drawConLines = function (ctx) {
    var i;
    var f;
    var l;
    var v;
    var hpos;
    var vpos;
    var y;
    var s;
    var key;
    var o;
    var parent;
    var first;
    var last;
    var vpos_start;
    var vpos_end;

    ctx.lineWidth = 1;
    ctx.strokeStyle = lineColor;
    ctx.beginPath();
    for (i = 0; i < nodes.length; i++) {
      hpos = nodes[i].hpos;
      vpos = nodes[i].vpos;

      // Top and left lines of siblings.
      if (nodes[i].parentix >= 0) {
        if (nodes[i].contype === 'u') {
          ctx.moveTo(hpos + boxWidth / 2, vpos);
          ctx.lineTo(hpos + boxWidth / 2, vpos - vSpace / 2);
        }
        if (nodes[i].contype === 'l') {
          ctx.moveTo(hpos + boxWidth, vpos + boxHeight / 2);
          ctx.lineTo(nodes[nodes[i].parentix].hpos + boxWidth / 2, vpos + boxHeight / 2);
        }
        if (nodes[i].contype === 'r') {
          ctx.moveTo(hpos, vpos + boxHeight / 2);
          ctx.lineTo(nodes[nodes[i].parentix].hpos + boxWidth / 2, vpos + boxHeight / 2);
        }
      }

      // Downline if any siblings.
      v = getEndOfDownline(i);
      if (v >= 0) {
        ctx.moveTo(hpos + boxWidth / 2, vpos + boxHeight);
        ctx.lineTo(hpos + boxWidth / 2, v);
      }

      // Horizontal line above multiple usibs.
      if (nodes[i].usib.length > 1) {
        var lines = [];

        // Iterate through all usibs per line.
        if (Object.keys(usibsPerLine).length > 0) {
          for (y in usibsPerLine) {
            if (usibsPerLine.hasOwnProperty(y)) {
              for (s in usibsPerLine[y]) {
                if (usibsPerLine[y].hasOwnProperty(s)) {
                  // Group them by parent and vpos.
                  key = nodes[s].parent + '-' + nodes[s].vpos;
                  if (typeof lines[key] === 'undefined') {
                    lines[key] = [];
                  }
                  lines[key].push(s);
                }
              }
            }
          }
        }

        // Iterate through all lines.
        if (Object.keys(lines).length > 0) {
          var lineCount = 0;
          for (l in lines) {
            if (lines.hasOwnProperty(l)) {
              // Get the first and last usib in the line.
              first = lines[l][0];
              last = lines[l][lines[l].length - 1];
              parent = nodes[first].parentix;

              // If there is only one usib in the line and it is not the root.
              if (first === last && parent > -1) {
                // Define start and end v positions to look for nodes lying
                // above this usib.
                vpos_start = nodes[first].vpos;
                vpos_end = nodes[parent].vpos + boxHeight + vSpace;

                for (var j = vpos_start; j > vpos_end; j--) {
                  o = getNodeAtUnequal(nodes[parent].hpos + boxWidth / 2, j, first);
                  if (o > -1) {
                    break;
                  }
                }

                // If the way is clear between this usib and its parent, draw
                // a connection line between them.
                if (o < 0) {
                  // Draw a line to the right up to the center of the parent.
                  ctx.moveTo(nodes[first].hpos + boxWidth / 2, nodes[first].vpos - vSpace / 2);
                  ctx.lineTo(nodes[parent].hpos + boxWidth / 2, nodes[first].vpos - vSpace / 2);
                  // Draw a line up to the parent.
                  ctx.moveTo(nodes[parent].hpos + boxWidth / 2, nodes[first].vpos - vSpace / 2);
                  ctx.lineTo(nodes[parent].hpos + boxWidth / 2, nodes[parent].vpos + boxHeight + vSpace / 2);
                }
              }

              // Position the line at the first usib in the line.
              ctx.moveTo(nodes[first].hpos + boxWidth / 2, nodes[first].vpos - vSpace / 2);

              // If the hpos of the last usib in the first line is smaller than
              // the hpos of its parent, draw the line up to its parent's hpos.
              if (lineCount === 0 && parent > -1 && nodes[last].hpos + boxWidth / 2 < nodes[parent].hpos + boxWidth / 2) {
                ctx.lineTo(nodes[parent].hpos + boxWidth / 2, nodes[first].vpos - vSpace / 2);
              }
              else {
                // Draw a line to the last usib in the line.
                ctx.lineTo(nodes[last].hpos + boxWidth / 2, nodes[first].vpos - vSpace / 2);
              }

              lineCount++;
            }
          }
        }
      }

      // Horizontal line above a single 'u' sib, if not aligned.
      if (nodes[i].usib.length === 1) {
        f = nodes[i].usib[0];

        ctx.moveTo(nodes[f].hpos + boxWidth / 2, nodes[f].vpos - vSpace / 2);
        ctx.lineTo(hpos + boxWidth / 2, nodes[f].vpos - vSpace / 2);
      }
    }
    ctx.stroke();
  };

  /**
   * Find the end of the downline.
   *
   * @param {int} p
   *   Index of a parent sib.
   *
   * @return {*}
   *   Vertical position in pixels.
   */
  getEndOfDownline = function (p) {
    var f;
    var l;
    var r;

    // If this node has u-sibs, the endpoint can be found from the vpos of the
    // first u-sib.
    if (nodes[p].usib.length > 0) {
      f = nodes[p].usib[0];
      return nodes[f].vpos - vSpace / 2;
    }

    // Find the lowest 'l' or 'r' sib.
    l = nodes[p].lsib.length;
    r = nodes[p].rsib.length;
    f = -1;
    if (l > 0 && r === 0) {
      f = nodes[p].lsib[l - 1];
    }
    if (l === 0 && r > 0) {
      f = nodes[p].rsib[r - 1];
    }
    if (l > 0 && r > 0) {
      l = nodes[p].lsib[l - 1];
      r = nodes[p].rsib[r - 1];
      if (nodes[l].vpos > nodes[r].vpos) {
        f = l;
      }
      else {
        f = r;
      }
    }

    if (f >= 0) {
      return nodes[f].vpos + boxHeight / 2;
    }

    return -1;
  };

  /**
   * Get the node at a certain position.
   *
   * @param {int} x
   *   X position in pixels.
   * @param {int} y
   *   Y position in pixels.
   *
   * @return {number}
   *   Index of found node or -1.
   */
  getNodeAt = function (x, y) {
    var i;
    var x2;
    var y2;

    x2 = x - boxWidth;
    y2 = y - boxHeight;

    for (i = 0; i < nodes.length; i++) {
      if (x > nodes[i].hpos && x2 < nodes[i].hpos && y > nodes[i].vpos && y2 < nodes[i].vpos) {
        return i;
      }
    }
    return -1;
  };

  /**
   * Get the node on the same position as the given node.
   *
   * @param {int} x
   *   X position in pixels.
   * @param {int} y
   *   Y position in pixels.
   * @param {int} u
   *   Index of node to compare with.
   *
   * @return {number}
   *   Index of found node or -1.
   */
  getNodeAtUnequal = function (x, y, u) {
    var i;
    var x2;
    var y2;

    x2 = x - boxWidth;
    y2 = y - boxHeight;

    for (i = 0; i < nodes.length; i++) {
      if (i !== u && x > nodes[i].hpos && x2 < nodes[i].hpos && y > nodes[i].vpos && y2 < nodes[i].vpos) {
        return i;
      }
    }
    return -1;
  };

  /**
   * Walk along the parents. If one is a lsib or rsib, return the index.
   *
   * @param {int} n
   *   Level number.
   *
   * @return {*}
   *   Index of node or -1.
   */
  underVSib = function (n) {
    while (n >= 0) {
      if (nodes[n].contype === 'l') {
        return n;
      }
      if (nodes[n].contype === 'r') {
        return n;
      }
      n = nodes[n].parentix;
    }
    return -1;
  };

  /**
   * Clean text.
   *
   * @param {String} tin
   *   The text to clean.
   *
   * @return {void|XML|string}
   *   Cleaned string.
   */
  cleanText = function (tin) {
    var i;

    // Remove leading spaces.
    i = 0;
    while (tin.charAt(i) === ' ' || tin.charAt(i) === '\t') {
      i++;
    }
    if (i > 0) {
      tin = tin.substr(i);
    }

    // Remove trailing spaces.
    i = tin.length;
    while (i > 0 && (tin.charAt(i - 1) === ' ' || tin.charAt(i - 1) === '\t')) {
      i--;
    }
    if (i < tin.length) {
      tin = tin.substr(0, i);
    }

    // Implode double spaces and tabs etc.
    return tin.replace(/[ \t]{2,}/g, ' ');
  };

  /**
   * Get the lowest box.
   *
   * @param {int} p
   *   Index of parent sib.
   * @param {String} subtree
   *   Type of tree ('u', 'l' or 'r').
   *
   * @return {*|number|int}
   *   Index of the lowest sib.
   */
  getLowestBox = function (p, subtree) {
    var s;
    var y;
    var r;

    if (typeof subtree === 'undefined') {
      subtree = 'ulr';
    }

    y = nodes[p].vpos;

    if (subtree.indexOf('u') >= 0) {
      for (s = 0; s < nodes[p].usib.length; s++) {
        r = getLowestBox(nodes[p].usib[s]);
        y = Math.max(r, y);
      }
    }

    if (subtree.indexOf('l') >= 0) {
      for (s = 0; s < nodes[p].lsib.length; s++) {
        r = getLowestBox(nodes[p].lsib[s]);
        y = Math.max(r, y);
      }
    }

    if (subtree.indexOf('r') >= 0) {
      for (s = 0; s < nodes[p].rsib.length; s++) {
        r = getLowestBox(nodes[p].rsib[s]);
        y = Math.max(r, y);
      }
    }

    return y;
  };

  /**
   * Get the root node of a given index.
   *
   * @param {int} p
   *   Index of parent sib.
   *
   * @return {*}
   *   Index of a root node.
   */
  getRootNode = function (p) {
    while (nodes[p].parent !== '') {
      p = nodes[p].parentix;
    }
    return p;
  };

  /**
   * Walk to the top of the tree, and return the first 'u' node found.
   *
   * @param {int} n
   *   Level number.
   *
   * @return {*}
   *   Index of first 'u' node found or root node if not found.
   */
  getUParent = function (n) {
    while (n >= 0) {
      if (nodes[n].contype === 'u' || nodes[n].parent === '') {
        return n;
      }
      n = nodes[n].parentix;
    }
    // Not reached.
    return -1;
  };

  /**
   * Check if a node is part of a given tree.
   *
   * @param {int} n
   *   Index of a node.
   * @param {int} p
   *   Index of the parent.
   *
   * @return {number}
   *   Return 1 if node n is part of the p tree.
   */
  nodeUnderParent = function (n, p) {
    while (n >= 0) {
      if (n === p) {
        return 1;
      }
      n = nodes[n].parentix;
    }
    return 0;
  };

  /**
   * Get the absolute x position of an object.
   *
   * @param {Object} obj
   *   The object to get the x position from.
   *
   * @return {number}
   *   Absolute x position.
   */
  getAbsPosX = function (obj) {
    var curleft = 0;

    if (obj.offsetParent) {
      do {
        curleft += obj.offsetLeft;
        obj = obj.offsetParent;
      } while (obj);
    }
    else {
      if (obj.x) {
        curleft += obj.x;
      }
    }

    return curleft;
  };

  /**
   * Get the absolute y position of an object.
   *
   * @param {Object} obj
   *   The object to get the y position from.
   *
   * @return {number}
   *   Absolute y position.
   */
  getAbsPosY = function (obj) {
    var curtop = 0;

    if (obj.offsetParent) {
      do {
        curtop += obj.offsetTop;
        obj = obj.offsetParent;
      } while (obj);
    }
    else {
      if (obj.y) {
        curtop += obj.y;
      }
    }

    return curtop;
  };

  /**
   * Shift the parent and all r-sibs to the right.
   *
   * @param {int} p
   *   Index of the parent.
   * @param {int} v
   *   Vertical position in pixels.
   */
  makeRoomForDownline = function (p, v) {
    var maxx;
    var h;
    var x;
    var w;
    var l;
    var r;

    // L-sib trees may not overlap the downline up to the point vpos.
    if (v > 0) {
      // Check 'l' sibs first.
      if (nodes[p].lsib.length > 0) {
        maxx = -1;
        for (h = 0; h < nodes[p].lsib.length; h++) {
          x = findRightMost(nodes[p].lsib[h], v);
          maxx = Math.max(x, maxx);

          // Override maxx if this function is called before all sibs are
          // positioned (so still have position -1).
          if (maxx < 0) {
            maxx = curshadowOffsetX;
          }

          // If the node found is the lsib itself or hShift is bigger than
          // hSpace, use hShift. Otherwise, use hSpace / 2.
          if (x === nodes[nodes[p].lsib[h]].hpos || hShift >= hSpace) {
            w = maxx + boxWidth / 2 + hShift - nodes[p].hpos;
          }
          else {
            w = maxx + boxWidth / 2 + hSpace / 2 - nodes[p].hpos;
          }

          if (w > 0) {
            nodes[p].hpos += w;
            for (r = 0; r < nodes[p].rsib.length; r++) {
              hShiftTree(nodes[p].rsib[r], w);
            }
            for (l = 0; l < h; l++) {
              hShiftTree(nodes[p].lsib[l], w);
            }
          }
        }
      }

      // If right trees, shift them to the right of the (repositioned) root
      // node. Be careful not to shift them back over other nodes, which can
      // happen if the parent has no usibs (and thus the left tree can extend
      // to the right).
      for (r = 0; r < nodes[p].rsib.length; r++) {
        x = findLeftMost(nodes[p].rsib[r], v);
        // If the node found is the rsib itself or hShift is bigger than hSpace,
        // use hShift. Otherwise use hSpace / 2.
        if (x === nodes[nodes[p].rsib[r]].hpos || hShift >= hSpace) {
          w = nodes[p].hpos + boxWidth / 2 + hShift - x;
        }
        else {
          w = nodes[p].hpos + boxWidth / 2 + hSpace / 2 - x;
        }
        if (w) {
          hShiftTree(nodes[p].rsib[r], w);
        }
      }
    }
  };

  /**
   * Center all nodes in the canvas.
   *
   * @param {int} width
   *   Canvas width in pixels.
   */
  centerOnCanvas = function (width) {
    var i;
    var max;
    var min;
    var w;

    // Find the left and rightmost nodes.
    max = -1;
    min = 999999;
    for (i = 0; i < nodes.length; i++) {
      if (nodes[i].hpos > max) {
        max = nodes[i].hpos;
      }
      if (nodes[i].hpos < min) {
        min = nodes[i].hpos;
      }
    }
    max += boxWidth;

    w = (width / 2) - (max - min) / 2;
    for (i = 0; i < nodes.length; i++) {
      nodes[i].hpos += w;
    }
  };

  /**
   * Position all nodes on the left side of the canvas.
   */
  leftOnCanvas = function () {
    var i;
    var min;
    var w;

    // Find the leftmost node.
    min = 999999;
    for (i = 0; i < nodes.length; i++) {
      if (nodes[i].hpos < min) {
        min = nodes[i].hpos;
      }
    }

    w = min;
    if (w > 0) {
      for (i = 0; i < nodes.length; i++) {
        nodes[i].hpos -= w;
      }
    }
  };

}
