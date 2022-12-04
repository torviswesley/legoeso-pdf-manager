<?php
/**
 * The admin area of the plugin to load the User List Table
 */
$upload_frm = ( isset($_REQUEST['pdm_upload_view']) && sanitize_text_field($_REQUEST['pdm_upload_view']) == '1') ? '': '_2';
/**
 * Get the value of the checkbox Force PDF Preview, the value overrides the current
 * setting set within the settings page
 */
$cb_force_img = $this->toggle_checkbox('off');
$force_image_enabled_value = $cb_force_img[0];
$force_image_enabled = $cb_force_img[1];
?>
<div id="col-container" class="wp-clearfix">

	<!-- /row-top -->

		<div class="wrap">
		<?php 
			/**
			* Include the file upload form.
			*/
			include "partials-display-upload-form{$upload_frm}.php";
		?>
		</div>
	<!-- /row-middle -->
		<div>
			<hr>
		</div>
	<!-- /row-bottom -->
		<?php 
			/**
			* Include the document list view.
			*/
			include 'partials-pdm-display-listview.php';
		?>
</div>