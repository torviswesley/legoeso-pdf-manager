<div class="wrap">    
	<h2><?php _e( 'Legoeso PDF Document Manager', $this->plugin_text_domain); ?></h2>
	<form id="pdm-list-form" method="post" name="pdm-list-form">
	<input type="hidden" name="page" value="<?php echo esc_attr($this->plugin_text_domain);?>" />
		<div id="pdf-doc-manager">			
			<div id="pdm-doc-list-table" style="">		
				<?php 
					wp_nonce_field( 'ajax-pdm-doc-list-nonce', '_ajax_pdm_doc_list_nonce' );
				?>
			</div>			
		</div>
	</form>
</div>