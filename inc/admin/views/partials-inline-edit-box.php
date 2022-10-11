<form method="post">
	<table style="display: none"><tbody id="inlineedit">
		
		<tr id="inline-edit" class="inline-edit-row inline-edit-row-page quick-edit-row quick-edit-row-page inline-edit-page" style="display: none">
			<td colspan="5" class="colspanchange">
			<div class="inline-edit-wrapper" role="region" aria-labelledby="quick-edit-legend">
				<fieldset class="inline-edit-col-left">
					<legend class="inline-edit-legend" id="quick-edit-legend">Quick Edit</legend>
					<div class="inline-edit-col">

							<label>
								<span class="title">Filename</span>
								<span class="input-text-wrap">
                                    <input type="text" id="" name="edit-document_filename" class="ptitle" value=""></span>
							</label>
							<label>
								<span class="title">Category</span>
                            <?php
							$args = array(
								'hide_empty'		=>	0,
								'show_option_none'	=>	'Select Category',
                                'selected'          =>   '',
								'orderby'			=>	'name',
								'order'				=>	'ASC',		
								'value_field'		=>	'name',
								'name'				=>	'edit-document_category',
								'id'				=>	'edit-document_category'
							);
							//  Using WordPress function to create dropdown menu of categories
							wp_dropdown_categories($args);
						?>
                                
							</label>
					</div>
				</fieldset>

                
				<div class="submit inline-edit-save">
                   
					<button type="button" class="button button-primary save">Update</button>
					<button type="button" class="button cancel">Cancel</button>
					<span class="spinner"></span>

					<div class="notice notice-error notice-alt inline hidden">
					<p class="error"></p>
				</div>
			</div> 
			<!-- end of .inline-edit-wrapper -->
		</td>
		</tr>
	</tbody></table>
	</form>