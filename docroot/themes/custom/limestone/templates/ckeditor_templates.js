
// Override the default template set
CKEDITOR.addTemplates( 'default', {
	// The name of sub folder which hold the shortcut preview images of the
	// templates.
	imagesPath: '/themes/custom/limestone/images/ckeditor/',

	// The templates definitions.
	templates: [ {
		title: 'Ping Pong Content',
		image: 'template1.gif',
		description: 'Image on left, Content on Right - Content on Left, Image on Right',
		html:
    '<div class="fill grid-x">' +
    	'<div class="large-6 cell">' +
				'<div class="cover-image">' +
					'<drupal-entity alt="Front Campus Curtis Building" data-embed-button="media_browser" data-entity-embed-display="media_image" data-entity-embed-display-settings="{&quot;image_style&quot;:&quot;scale_1600&quot;,&quot;image_link&quot;:&quot;&quot;}" data-entity-type="media" data-entity-uuid="f83c2d5c-5ff1-4848-a216-a315b9b00277" title="Campus Front Curtis Building"></drupal-entity>' +
				'</div>' +
    	'</div>' +
			'<div class="large-6 cell spacing-sm padding-sides-sm">' +
				'<div class="fill">' +
					'<h2 class="text-align-center h1 big-title">ONE <span class="font-reg">FOCUS</span></h2>' +
					'<p>There’s only ONE you, unique and brimming with potential. We focus on your skills and dreams to shine a spotlight on what makes you who you are - a serious student, charismatic leader, gifted athlete, or talented artist. Our ONE focus is your education.</p>' +
					'<p class="h4 bold margin-bottom-2">There’s only ONE you. There’s only ONE Limestone.</p>' +
				'</div>' +
			'</div>' +
    '</div>' +
    '<div class="fill grid-x">' +
			'<div class="large-6 cell">' +
				'<div class="cover-image">' +
					'<drupal-entity alt="Front Campus Curtis Building" data-embed-button="media_browser" data-entity-embed-display="media_image" data-entity-embed-display-settings="{&quot;image_style&quot;:&quot;scale_1600&quot;,&quot;image_link&quot;:&quot;&quot;}" data-entity-type="media" data-entity-uuid="f83c2d5c-5ff1-4848-a216-a315b9b00277" title="Campus Front Curtis Building"></drupal-entity>' +
				'</div>' +
			'</div>' +
			'<div class="large-6 cell spacing-sm padding-sides-sm">' +
				'<div class="fill">' +
					'<h2 class="text-align-center h1 big-title">ONE <span class="font-reg">FOCUS</span></h2>' +
					'<p>There’s only ONE you, unique and brimming with potential. We focus on your skills and dreams to shine a spotlight on what makes you who you are - a serious student, charismatic leader, gifted athlete, or talented artist. Our ONE focus is your education.</p>' +
					'<p class="h4 bold margin-bottom-2">There’s only ONE you. There’s only ONE Limestone.</p>' +
				'</div>' +
			'</div>' +
    '</div>'
	},
	{
		title: 'Text and Table',
		image: 'template3.gif',
		description: 'A title with some text and a table.',
		html: '<div style="width: 80%">' +
			'<h3>' +
				'Title goes here' +
			'</h3>' +
			'<table style="width:150px;float: right" cellspacing="0" cellpadding="0" border="1">' +
				'<caption style="border:solid 1px black">' +
					'<strong>Table title</strong>' +
				'</caption>' +
				'<tr>' +
					'<td>&nbsp;</td>' +
					'<td>&nbsp;</td>' +
					'<td>&nbsp;</td>' +
				'</tr>' +
				'<tr>' +
					'<td>&nbsp;</td>' +
					'<td>&nbsp;</td>' +
					'<td>&nbsp;</td>' +
				'</tr>' +
				'<tr>' +
					'<td>&nbsp;</td>' +
					'<td>&nbsp;</td>' +
					'<td>&nbsp;</td>' +
				'</tr>' +
			'</table>' +
			'<p>' +
				'Type the text here' +
			'</p>' +
			'</div>'
	},
		{
			title: 'AddToAny Buttons',
			image: 'addtoany_buttons.jpg',
			description: 'An easy way to manually put share buttons on the page.',
			html: '<div class="a2a_kit a2a_kit_size_32 a2a_default_style centered spacing-bottom-xs" style="width:12rem;">' +
					'<a class="a2a_dd" href="https://www.addtoany.com/share"></a>' +
					'<a class="a2a_button_facebook"></a>' +
					'<a class="a2a_button_twitter"></a>' +
					'<a class="a2a_button_linkedin"></a>' +
					'<a class="a2a_button_email"></a>' +
					'<a class="a2a_button_copy_link"></a>' +
					'</div>' +
					'<script async src="https://static.addtoany.com/menu/page.js"></script>'
		} ]
} );
