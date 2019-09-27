<div class="sidebar">
	<?php
		if ( is_page() ) {
			dynamic_sidebar( 'page' );
		} else {
			dynamic_sidebar( 'blog' );
		}
	?>
</div>
