<div class="container pt-4"> 
	<?php if($python_error == 1) {
	?>
	<strong style="color:red;"> <?php echo $python_version ;?></strong>
	<?php
		} 
	?>
	<p class="h1"> Legoeso PDF | Enviroment Variables</p>

	<?php 
		wp_nonce_field( 'pdm-doc-settings-nonce', '_pdm_doc_settings_nonce' ); 
		$sys_info = $this->check_dependencies();
		include 'load-settings-page.php';
	?>

		<br>

</div>
