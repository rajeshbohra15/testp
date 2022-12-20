<form role="search" method="get" id="searchform" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<div><label class="screen-reader-text" for="s"><?php esc_html_e( 'Search for:', 'estera' ); ?></label>
		<input type="search" value="" placeholder="<?php esc_attr_e( 'Search...', 'estera' ); ?>" name="s" id="s"/>
        
        <button type="submit" id="searchsubmit" class="icon_search"></button>
	</div>
</form>