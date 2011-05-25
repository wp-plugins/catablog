<h5><?php _e('Show on screen') ?></h5>
<div class='screen-options'>
	<input type='text' class='screen-per-page' name='wp_screen_options[value]' id='upload_per_page' maxlength='3' value='20' /> 
	<label for='upload_per_page'><?php _e('Catalog items', 'catablog') ?></label>
	<input type="submit" name="screen-options-apply" id="screen-options-apply" class="button" value="<?php _e('Apply') ?>"  />
	<input type='hidden' name='wp_screen_options[option]' value='upload_per_page' />
</div>
<div>
	<input type="hidden" id="screenoptionnonce" name="screenoptionnonce" value="f14497ef51" />
</div>