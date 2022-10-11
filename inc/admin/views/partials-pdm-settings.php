<div class="container pt-4"> 
	<?php if($python_error == 1) {
		?>
<strong style="color:red;"> <?php echo $python_version ;?></strong>
	<?php
	} 
	?>
<p class="h1"> Legoeso PDF | Enviroment Variables</p>
<form name='form_pdm_doc_settings' method="post">
	<?php wp_nonce_field( 'pdm-doc-settings-nonce', '_pdm_doc_settings_nonce' ); ?>
		<?php include 'load-settings-page.php'; ?>
		<br>
	<div class="col-auto">
		<button type="submit" class="btn btn-primary mb-3 justify-content-md-end">Confirm Settings</button>
	</div>
</form>
</div>
