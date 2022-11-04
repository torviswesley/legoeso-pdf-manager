(function ($) {
	'use strict';

	/**
     * Legoeso PDF Document Manager	
	 * The file is enqueued from inc/admin/class-admin.php.
     * 
	 *
	 * @since 1.2.0
	 *
	 *
	 */
	var icons = {
		"header": "ui-icon-circle-arrow-e",
		"activeHeader": "ui-icon-circle-arrow-s",
	};

	$("#accordion").accordion({
		icons: icons,
		collapsible: true,
		heightStyle: "content",
	});

})(jQuery);