<?php
/*
**** Meta box display callback ****
*/
function citation_callback( $post ) {
	
	$citations = get_post_meta( $post->ID, 'citations', true );
	
	// Usaremos este nonce field más adelante cuando guardemos en twp_save_meta_box()
	wp_nonce_field( 'mi_meta_box_nonce', 'meta_box_nonce' );
	
	echo '<p><textarea style="width:100%;" name="citations" id="citations" rows="10" cols="50">'. $citations .' </textarea></p>';
}